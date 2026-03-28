<?php

declare(strict_types=1);

namespace OOPress\Content;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * ContentRepository — Database operations for content.
 * 
 * @api
 */
class ContentRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {}
    
    /**
     * Find content by ID.
     */
    public function find(int $id, ?string $language = null): ?Content
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('c.*', 't.*')
            ->from('oop_content', 'c')
            ->leftJoin('c', 'oop_content_translations', 't', 'c.id = t.content_id')
            ->where('c.id = :id')
            ->setParameter('id', $id);
        
        if ($language !== null) {
            $query->andWhere('t.language = :language')
                  ->setParameter('language', $language);
        }
        
        $rows = $query->executeQuery()->fetchAllAssociative();
        
        if (empty($rows)) {
            return null;
        }
        
        return $this->hydrateContent($rows);
    }
    
    /**
     * Find content by slug and language.
     */
    public function findBySlug(string $slug, string $language): ?Content
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('c.*', 't.*')
            ->from('oop_content', 'c')
            ->leftJoin('c', 'oop_content_translations', 't', 'c.id = t.content_id')
            ->where('t.slug = :slug')
            ->andWhere('t.language = :language')
            ->setParameter('slug', $slug)
            ->setParameter('language', $language);
        
        $rows = $query->executeQuery()->fetchAllAssociative();
        
        if (empty($rows)) {
            return null;
        }
        
        return $this->hydrateContent($rows);
    }
    
    /**
     * Find all content of a type.
     * 
     * @return array<int, Content>
     */
    public function findByType(string $contentType, ?string $language = null, int $limit = 50, int $offset = 0): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('c.*', 't.*')
            ->from('oop_content', 'c')
            ->leftJoin('c', 'oop_content_translations', 't', 'c.id = t.content_id')
            ->where('c.content_type = :type')
            ->setParameter('type', $contentType)
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        if ($language !== null) {
            $query->andWhere('t.language = :language')
                  ->setParameter('language', $language);
        }
        
        $rows = $query->executeQuery()->fetchAllAssociative();
        
        return $this->hydrateMultiple($rows);
    }
    
    /**
     * Save a content entity with its translations.
     */
    public function save(Content $content): void
    {
        $this->connection->beginTransaction();
        
        try {
            // Update or insert base content
            $baseData = [
                'content_type' => $content->contentType,
                'author_id' => $content->authorId,
                'status' => $content->status,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            if ($content->id === 0) {
                $baseData['created_at'] = date('Y-m-d H:i:s');
                $this->connection->insert('oop_content', $baseData);
                $contentId = (int) $this->connection->lastInsertId();
            } else {
                $this->connection->update('oop_content', $baseData, ['id' => $content->id]);
                $contentId = $content->id;
            }
            
            // Save translations
            foreach ($content->getTranslations() as $translation) {
                $translationData = [
                    'content_id' => $contentId,
                    'language' => $translation->language,
                    'title' => $translation->title,
                    'slug' => $translation->slug,
                    'body' => $translation->body,
                    'summary' => $translation->summary,
                    'is_default' => $translation->isDefault ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                
                if ($translation->id === 0) {
                    $translationData['created_at'] = date('Y-m-d H:i:s');
                    $this->connection->insert('oop_content_translations', $translationData);
                } else {
                    $this->connection->update(
                        'oop_content_translations',
                        $translationData,
                        ['id' => $translation->id]
                    );
                }
            }
            
            $this->connection->commit();
            
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
    
    /**
     * Delete content and all its translations.
     */
    public function delete(Content $content): void
    {
        $this->connection->delete('oop_content', ['id' => $content->id]);
    }
    
    /**
     * Hydrate a content entity from database rows.
     */
    private function hydrateContent(array $rows): Content
    {
        $firstRow = $rows[0];
        
        $content = new Content(
            id: (int) $firstRow['id'],
            contentType: $firstRow['content_type'],
            authorId: (int) $firstRow['author_id'],
            status: $firstRow['status'],
            createdAt: new \DateTimeImmutable($firstRow['created_at']),
            updatedAt: new \DateTimeImmutable($firstRow['updated_at']),
            publishedAt: $firstRow['published_at'] ? new \DateTimeImmutable($firstRow['published_at']) : null,
        );
        
        foreach ($rows as $row) {
            if ($row['content_id'] === null) {
                continue;
            }
            
            $translation = new ContentTranslation(
                id: (int) $row['translation_id'] ?? 0,
                contentId: (int) $row['id'],
                language: $row['language'],
                title: $row['title'],
                slug: $row['slug'],
                body: $row['body'],
                summary: $row['summary'],
                isDefault: (bool) $row['is_default'],
                fields: $this->hydrateFields((int) $row['id'], $row['language']),
                createdAt: new \DateTimeImmutable($row['translation_created_at'] ?? $row['created_at']),
                updatedAt: new \DateTimeImmutable($row['translation_updated_at'] ?? $row['updated_at']),
            );
            
            $content->addTranslation($translation);
        }
        
        return $content;
    }
    
    /**
     * Hydrate multiple content entities.
     * 
     * @return array<int, Content>
     */
    private function hydrateMultiple(array $rows): array
    {
        $contents = [];
        $grouped = [];
        
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [];
            }
            $grouped[$id][] = $row;
        }
        
        foreach ($grouped as $rows) {
            $content = $this->hydrateContent($rows);
            $contents[$content->id] = $content;
        }
        
        return $contents;
    }
    
    /**
     * Hydrate extended field values.
     */
    private function hydrateFields(int $contentId, string $language): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('fd.field_name', 'fv.value')
            ->from('oop_field_values', 'fv')
            ->leftJoin('fv', 'oop_field_definitions', 'fd', 'fv.field_id = fd.id')
            ->where('fv.content_id = :content_id')
            ->andWhere('fv.language = :language')
            ->setParameter('content_id', $contentId)
            ->setParameter('language', $language);
        
        $rows = $query->executeQuery()->fetchAllAssociative();
        $fields = [];
        
        foreach ($rows as $row) {
            $fields[$row['field_name']] = json_decode($row['value'], true);
        }
        
        return $fields;
    }
}
