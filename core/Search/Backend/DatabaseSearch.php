<?php

declare(strict_types=1);

namespace OOPress\Search\Backend;

use Doctrine\DBAL\Connection;
use OOPress\Search\SearchInterface;
use OOPress\Search\IndexableInterface;
use OOPress\Search\SearchQuery;
use OOPress\Search\SearchResult;
use OOPress\Search\SearchResultCollection;

/**
 * DatabaseSearch — Database-based search backend.
 * 
 * GDPR compliant: All data stays in your database. No external services.
 * 
 * @api
 */
class DatabaseSearch implements SearchInterface
{
    private const TABLE_NAME = 'oop_search_index';
    
    public function __construct(
        private readonly Connection $connection,
    ) {
        $this->ensureTableExists();
    }
    
    /**
     * Ensure search index table exists.
     */
    private function ensureTableExists(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        
        if ($schemaManager->tablesExist([self::TABLE_NAME])) {
            return;
        }
        
        $schemaManager->createTable(
            $schemaManager->createSchemaConfig()->createTable(self::TABLE_NAME)
                ->addColumn('id', 'integer', ['autoincrement' => true])
                ->addColumn('document_id', 'string', ['length' => 255])
                ->addColumn('type', 'string', ['length' => 50])
                ->addColumn('title', 'string', ['length' => 500])
                ->addColumn('content', 'text')
                ->addColumn('url', 'string', ['length' => 500])
                ->addColumn('language', 'string', ['length' => 10, 'notnull' => false])
                ->addColumn('fields', 'text', ['notnull' => false])
                ->addColumn('access_roles', 'text', ['notnull' => false])
                ->addColumn('access_user_id', 'integer', ['notnull' => false])
                ->addColumn('created_at', 'datetime')
                ->addColumn('updated_at', 'datetime')
                ->setPrimaryKey(['id'])
                ->addUniqueIndex(['document_id', 'type'], 'uniq_document_type')
                ->addIndex(['type'], 'idx_type')
                ->addIndex(['language'], 'idx_language')
        );
    }
    
    /**
     * Index a document.
     */
    public function index(IndexableInterface $document): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT id FROM ' . self::TABLE_NAME . ' WHERE document_id = :id AND type = :type',
            ['id' => $document->getSearchId(), 'type' => $document->getSearchType()]
        );
        
        $data = [
            'document_id' => $document->getSearchId(),
            'type' => $document->getSearchType(),
            'title' => $document->getSearchTitle(),
            'content' => $document->getSearchContent(),
            'url' => $document->getSearchUrl(),
            'fields' => json_encode($document->getSearchFields()),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $access = $document->getSearchAccess();
        $data['access_roles'] = json_encode($access['roles'] ?? []);
        $data['access_user_id'] = $access['user_id'] ?? null;
        
        if ($exists) {
            $this->connection->update(self::TABLE_NAME, $data, [
                'document_id' => $document->getSearchId(),
                'type' => $document->getSearchType(),
            ]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->connection->insert(self::TABLE_NAME, $data);
        }
    }
    
    /**
     * Remove a document from index.
     */
    public function remove(IndexableInterface $document): void
    {
        $this->connection->delete(self::TABLE_NAME, [
            'document_id' => $document->getSearchId(),
            'type' => $document->getSearchType(),
        ]);
    }
    
    /**
     * Search for documents.
     */
    public function search(SearchQuery $query): SearchResultCollection
    {
        $keyword = $query->getKeyword();
        
        if (empty($keyword)) {
            return new SearchResultCollection([], 0);
        }
        
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from(self::TABLE_NAME, 's')
            ->where('MATCH(title, content) AGAINST (:keyword IN BOOLEAN MODE)')
            ->setParameter('keyword', $this->prepareKeyword($keyword))
            ->orderBy('relevance', 'DESC')
            ->setFirstResult($query->getOffset())
            ->setMaxResults($query->getLimit());
        
        // Add type filter
        $types = $query->getTypes();
        if (!empty($types)) {
            $qb->andWhere('type IN (:types)')
                ->setParameter('types', $types, Connection::PARAM_STR_ARRAY);
        }
        
        // Add language filter
        if ($query->getLanguage()) {
            $qb->andWhere('language = :language OR language IS NULL')
                ->setParameter('language', $query->getLanguage());
        }
        
        // Add access control
        $roles = $query->getUserRoles() ?? ['anonymous'];
        $userId = $query->getUserId();
        
        $accessCondition = '(access_roles IS NULL OR JSON_OVERLAPS(access_roles, :roles) = 1)';
        $qb->andWhere($accessCondition)
            ->setParameter('roles', json_encode($roles));
        
        if ($userId) {
            $qb->andWhere('(access_user_id IS NULL OR access_user_id = :user_id)')
                ->setParameter('user_id', $userId);
        } else {
            $qb->andWhere('access_user_id IS NULL');
        }
        
        // Execute query
        $rows = $qb->executeQuery()->fetchAllAssociative();
        
        // Count total
        $countQb = clone $qb;
        $countQb->select('COUNT(*)')
            ->setFirstResult(0)
            ->setMaxResults(null);
        $total = (int) $countQb->executeQuery()->fetchOne();
        
        // Build results
        $results = [];
        foreach ($rows as $row) {
            $results[] = new SearchResult(
                id: $row['document_id'],
                type: $row['type'],
                title: $this->highlight($row['title'], $keyword),
                url: $row['url'],
                excerpt: $this->highlight($this->getExcerpt($row['content'], $keyword), $keyword),
                score: 1.0,
                fields: json_decode($row['fields'] ?? '[]', true),
                highlights: [],
            );
        }
        
        return new SearchResultCollection($results, $total);
    }
    
    /**
     * Rebuild the entire index.
     */
    public function rebuild(): void
    {
        $this->clear();
        
        // Dispatch event for modules to index their content
        $event = new \OOPress\Search\Event\RebuildIndexEvent($this);
        $dispatcher = \OOPress\Kernel::getInstance()->getHookDispatcher();
        $dispatcher->dispatch($event, 'search.rebuild');
    }
    
    /**
     * Clear the index.
     */
    public function clear(): void
    {
        $this->connection->executeStatement('TRUNCATE TABLE ' . self::TABLE_NAME);
    }
    
    /**
     * Get index statistics.
     */
    public function getStats(): array
    {
        $count = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ' . self::TABLE_NAME
        );
        
        $byType = $this->connection->fetchAllAssociative(
            'SELECT type, COUNT(*) as count FROM ' . self::TABLE_NAME . ' GROUP BY type'
        );
        
        return [
            'total_documents' => $count,
            'by_type' => $byType,
            'backend' => 'database',
        ];
    }
    
    /**
     * Check if search backend is available.
     */
    public function isAvailable(): bool
    {
        try {
            $schemaManager = $this->connection->createSchemaManager();
            return $schemaManager->tablesExist([self::TABLE_NAME]);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Prepare keyword for MySQL boolean mode search.
     */
    private function prepareKeyword(string $keyword): string
    {
        // Remove special characters
        $keyword = preg_replace('/[+\-<>~*()"@]/', ' ', $keyword);
        $words = preg_split('/\s+/', $keyword);
        
        // Add + operator for each word
        $prepared = '+' . implode(' +', array_filter($words));
        
        return $prepared;
    }
    
    /**
     * Get excerpt with keyword highlighting.
     */
    private function getExcerpt(string $content, string $keyword, int $length = 200): string
    {
        $words = preg_split('/\s+/', $keyword);
        $content = strip_tags($content);
        
        // Find position of first keyword
        $firstPos = -1;
        foreach ($words as $word) {
            $pos = stripos($content, $word);
            if ($pos !== false && ($firstPos === -1 || $pos < $firstPos)) {
                $firstPos = $pos;
            }
        }
        
        if ($firstPos === -1) {
            return substr($content, 0, $length) . (strlen($content) > $length ? '...' : '');
        }
        
        // Extract around the keyword
        $start = max(0, $firstPos - 50);
        $excerpt = substr($content, $start, $length);
        
        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }
        
        if (strlen($content) > $start + $length) {
            $excerpt .= '...';
        }
        
        return $excerpt;
    }
    
    /**
     * Highlight keywords in text.
     */
    private function highlight(string $text, string $keyword): string
    {
        $words = preg_split('/\s+/', $keyword);
        $pattern = '/(' . implode('|', array_map('preg_quote', $words)) . ')/i';
        
        return preg_replace($pattern, '<mark>$1</mark>', $text);
    }
}