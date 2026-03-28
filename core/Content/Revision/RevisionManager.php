<?php

declare(strict_types=1);

namespace OOPress\Content\Revision;

use Doctrine\DBAL\Connection;
use OOPress\Content\Content;
use OOPress\Content\ContentTranslation;

/**
 * RevisionManager — Handles content versioning.
 * 
 * @api
 */
class RevisionManager
{
    public function __construct(
        private readonly Connection $connection,
    ) {}
    
    /**
     * Create a revision from a content translation.
     */
    public function createRevision(Content $content, ContentTranslation $translation): Revision
    {
        $nextNumber = $this->getNextRevisionNumber($content->id);
        
        $this->connection->insert('oop_content_revisions', [
            'content_id' => $content->id,
            'revision_number' => $nextNumber,
            'title' => $translation->title,
            'slug' => $translation->slug,
            'body' => $translation->body,
            'fields_data' => json_encode($translation->fields),
            'author_id' => $content->authorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        $revisionId = (int) $this->connection->lastInsertId();
        
        return new Revision(
            id: $revisionId,
            contentId: $content->id,
            revisionNumber: $nextNumber,
            title: $translation->title,
            slug: $translation->slug,
            body: $translation->body,
            fields: $translation->fields,
            authorId: $content->authorId,
            createdAt: new \DateTimeImmutable(),
        );
    }
    
    /**
     * Get all revisions for a content item.
     * 
     * @return array<int, Revision>
     */
    public function getRevisions(int $contentId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('oop_content_revisions')
            ->where('content_id = :content_id')
            ->orderBy('revision_number', 'DESC')
            ->setParameter('content_id', $contentId);
        
        $rows = $query->executeQuery()->fetchAllAssociative();
        $revisions = [];
        
        foreach ($rows as $row) {
            $revisions[] = new Revision(
                id: (int) $row['id'],
                contentId: (int) $row['content_id'],
                revisionNumber: (int) $row['revision_number'],
                title: $row['title'],
                slug: $row['slug'],
                body: $row['body'],
                fields: json_decode($row['fields_data'], true),
                authorId: (int) $row['author_id'],
                createdAt: new \DateTimeImmutable($row['created_at']),
            );
        }
        
        return $revisions;
    }
    
    /**
     * Get a specific revision.
     */
    public function getRevision(int $contentId, int $revisionNumber): ?Revision
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('oop_content_revisions')
            ->where('content_id = :content_id')
            ->andWhere('revision_number = :revision_number')
            ->setParameter('content_id', $contentId)
            ->setParameter('revision_number', $revisionNumber);
        
        $row = $query->executeQuery()->fetchAssociative();
        
        if (!$row) {
            return null;
        }
        
        return new Revision(
            id: (int) $row['id'],
            contentId: (int) $row['content_id'],
            revisionNumber: (int) $row['revision_number'],
            title: $row['title'],
            slug: $row['slug'],
            body: $row['body'],
            fields: json_decode($row['fields_data'], true),
            authorId: (int) $row['author_id'],
            createdAt: new \DateTimeImmutable($row['created_at']),
        );
    }
    
    /**
     * Restore a revision.
     */
    public function restoreRevision(Revision $revision): void
    {
        $this->connection->update(
            'oop_content_translations',
            [
                'title' => $revision->title,
                'slug' => $revision->slug,
                'body' => $revision->body,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'content_id' => $revision->contentId,
            ]
        );
        
        // Restore field values
        foreach ($revision->fields as $fieldName => $value) {
            $this->connection->update(
                'oop_field_values',
                ['value' => json_encode($value)],
                [
                    'content_id' => $revision->contentId,
                ]
            );
        }
        
        // Create a new revision for the restore operation
        $this->connection->insert('oop_content_revisions', [
            'content_id' => $revision->contentId,
            'revision_number' => $this->getNextRevisionNumber($revision->contentId),
            'title' => $revision->title,
            'slug' => $revision->slug,
            'body' => $revision->body,
            'fields_data' => json_encode($revision->fields),
            'author_id' => $revision->authorId,
            'created_at' => date('Y-m-d H:i:s'),
            'restored_from' => $revision->revisionNumber,
        ]);
    }
    
    /**
     * Get the next revision number for a content item.
     */
    private function getNextRevisionNumber(int $contentId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('MAX(revision_number) as max_revision')
            ->from('oop_content_revisions')
            ->where('content_id = :content_id')
            ->setParameter('content_id', $contentId);
        
        $result = $query->executeQuery()->fetchOne();
        
        return $result ? (int) $result + 1 : 1;
    }
}
