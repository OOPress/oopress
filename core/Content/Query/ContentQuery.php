<?php

declare(strict_types=1);

namespace OOPress\Content\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use OOPress\Content\ContentRepository;

/**
 * ContentQuery — Fluent query builder for content.
 * 
 * @api
 */
class ContentQuery
{
    private DoctrineQueryBuilder $queryBuilder;
    private ?string $language = null;
    private ?string $contentType = null;
    private array $conditions = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    
    public function __construct(
        private readonly Connection $connection,
        private readonly ContentRepository $repository,
    ) {
        $this->queryBuilder = $connection->createQueryBuilder();
        $this->reset();
    }
    
    /**
     * Reset the query to its initial state.
     */
    public function reset(): self
    {
        $this->language = null;
        $this->contentType = null;
        $this->conditions = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        
        return $this;
    }
    
    /**
     * Set the language for the query.
     */
    public function language(string $language): self
    {
        $this->language = $language;
        return $this;
    }
    
    /**
     * Filter by content type.
     */
    public function type(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }
    
    /**
     * Filter by status.
     */
    public function status(string $status): self
    {
        return $this->where('c.status', '=', $status);
    }
    
    /**
     * Filter by author.
     */
    public function author(int $authorId): self
    {
        return $this->where('c.author_id', '=', $authorId);
    }
    
    /**
     * Filter by field value.
     */
    public function fieldEquals(string $fieldName, mixed $value, string $language = null): self
    {
        $this->conditions[] = [
            'type' => 'field',
            'operator' => '=',
            'field' => $fieldName,
            'value' => $value,
            'language' => $language ?? $this->language,
        ];
        
        return $this;
    }
    
    /**
     * Filter by slug.
     */
    public function slug(string $slug): self
    {
        return $this->where('t.slug', '=', $slug);
    }
    
    /**
     * Search in title and body.
     */
    public function search(string $keyword): self
    {
        $this->conditions[] = [
            'type' => 'search',
            'keyword' => $keyword,
        ];
        
        return $this;
    }
    
    /**
     * Add a where condition.
     */
    public function where(string $field, string $operator, mixed $value): self
    {
        $this->conditions[] = [
            'type' => 'simple',
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];
        
        return $this;
    }
    
    /**
     * Add an order by clause.
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->orderBy[] = ['field' => $field, 'direction' => $direction];
        return $this;
    }
    
    /**
     * Set the limit.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * Set the offset.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * Execute the query and return results.
     * 
     * @return array<int, \OOPress\Content\Content>
     */
    public function execute(): array
    {
        $this->buildQuery();
        
        $rows = $this->queryBuilder->executeQuery()->fetchAllAssociative();
        
        if (empty($rows)) {
            return [];
        }
        
        // Group by content ID
        $grouped = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [];
            }
            $grouped[$id][] = $row;
        }
        
        // Hydrate content objects
        $results = [];
        foreach ($grouped as $rows) {
            $content = $this->hydrateFromRows($rows);
            if ($content) {
                $results[$content->id] = $content;
            }
        }
        
        return $results;
    }
    
    /**
     * Execute the query and return the first result.
     */
    public function first(): ?\OOPress\Content\Content
    {
        $results = $this->limit(1)->execute();
        return $results ? reset($results) : null;
    }
    
    /**
     * Count results without fetching them.
     */
    public function count(): int
    {
        $this->buildQuery(true);
        
        $row = $this->queryBuilder->executeQuery()->fetchAssociative();
        return $row ? (int) $row['count'] : 0;
    }
    
    /**
     * Build the query.
     */
    private function buildQuery(bool $countOnly = false): void
    {
        $this->queryBuilder = $this->connection->createQueryBuilder();
        
        if ($countOnly) {
            $this->queryBuilder->select('COUNT(DISTINCT c.id) as count');
        } else {
            $this->queryBuilder->select('c.*', 't.*');
        }
        
        $this->queryBuilder
            ->from('oop_content', 'c')
            ->leftJoin('c', 'oop_content_translations', 't', 'c.id = t.content_id');
        
        // Apply language filter
        if ($this->language !== null) {
            $this->queryBuilder->andWhere('t.language = :language')
                ->setParameter('language', $this->language);
        }
        
        // Apply content type filter
        if ($this->contentType !== null) {
            $this->queryBuilder->andWhere('c.content_type = :content_type')
                ->setParameter('content_type', $this->contentType);
        }
        
        // Apply conditions
        $this->applyConditions();
        
        // Apply ordering
        if (!$countOnly) {
            foreach ($this->orderBy as $order) {
                $field = $this->mapFieldToColumn($order['field']);
                $this->queryBuilder->addOrderBy($field, $order['direction']);
            }
        }
        
        // Apply limits
        if (!$countOnly && $this->limit !== null) {
            $this->queryBuilder->setMaxResults($this->limit);
        }
        if (!$countOnly && $this->offset !== null) {
            $this->queryBuilder->setFirstResult($this->offset);
        }
    }
    
    /**
     * Apply all conditions to the query builder.
     */
    private function applyConditions(): void
    {
        foreach ($this->conditions as $condition) {
            switch ($condition['type']) {
                case 'simple':
                    $field = $this->mapFieldToColumn($condition['field']);
                    $this->queryBuilder->andWhere(
                        $this->queryBuilder->expr()->comparison(
                            $field,
                            $condition['operator'],
                            $this->queryBuilder->createNamedParameter($condition['value'])
                        )
                    );
                    break;
                    
                case 'field':
                    $this->applyFieldCondition($condition);
                    break;
                    
                case 'search':
                    $this->applySearchCondition($condition['keyword']);
                    break;
            }
        }
    }
    
    /**
     * Apply a field value condition.
     */
    private function applyFieldCondition(array $condition): void
    {
        $lang = $condition['language'] ?? $this->language;
        
        $subQuery = $this->connection->createQueryBuilder();
        $subQuery
            ->select('content_id')
            ->from('oop_field_values', 'fv')
            ->leftJoin('fv', 'oop_field_definitions', 'fd', 'fv.field_id = fd.id')
            ->where('fd.field_name = :field_name')
            ->andWhere('fv.value = :value')
            ->setParameter('field_name', $condition['field'])
            ->setParameter('value', json_encode($condition['value']));
        
        if ($lang) {
            $subQuery->andWhere('fv.language = :lang')
                ->setParameter('lang', $lang);
        }
        
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->in('c.id', $subQuery->getSQL())
        );
    }
    
    /**
     * Apply a search condition.
     */
    private function applySearchCondition(string $keyword): void
    {
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->or(
                $this->queryBuilder->expr()->like('t.title', ':keyword'),
                $this->queryBuilder->expr()->like('t.body', ':keyword')
            )
        )->setParameter('keyword', '%' . $keyword . '%');
    }
    
    /**
     * Map a logical field name to a database column.
     */
    private function mapFieldToColumn(string $field): string
    {
        return match ($field) {
            'id' => 'c.id',
            'type', 'content_type' => 'c.content_type',
            'author', 'author_id' => 'c.author_id',
            'status' => 'c.status',
            'created', 'created_at' => 'c.created_at',
            'updated', 'updated_at' => 'c.updated_at',
            'published', 'published_at' => 'c.published_at',
            'title' => 't.title',
            'slug' => 't.slug',
            'body' => 't.body',
            'summary' => 't.summary',
            'language' => 't.language',
            default => "t.$field",
        };
    }
    
    /**
     * Hydrate content from query rows.
     */
    private function hydrateFromRows(array $rows): ?\OOPress\Content\Content
    {
        $firstRow = $rows[0];
        
        $content = new \OOPress\Content\Content(
            id: (int) $firstRow['id'],
            contentType: $firstRow['content_type'],
            authorId: (int) $firstRow['author_id'],
            status: $firstRow['status'],
            createdAt: new \DateTimeImmutable($firstRow['created_at']),
            updatedAt: new \DateTimeImmutable($firstRow['updated_at']),
            publishedAt: $firstRow['published_at'] ? new \DateTimeImmutable($firstRow['published_at']) : null,
        );
        
        foreach ($rows as $row) {
            if (!$row['language']) {
                continue;
            }
            
            $translation = new \OOPress\Content\ContentTranslation(
                id: (int) ($row['translation_id'] ?? 0),
                contentId: (int) $row['id'],
                language: $row['language'],
                title: $row['title'],
                slug: $row['slug'],
                body: $row['body'],
                summary: $row['summary'],
                isDefault: (bool) ($row['is_default'] ?? false),
                fields: [], // Fields loaded separately
                createdAt: new \DateTimeImmutable($row['translation_created_at'] ?? $row['created_at']),
                updatedAt: new \DateTimeImmutable($row['translation_updated_at'] ?? $row['updated_at']),
            );
            
            $content->addTranslation($translation);
        }
        
        return $content;
    }
}
