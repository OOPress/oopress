<?php

declare(strict_types=1);

namespace OOPress\Media;

use Doctrine\DBAL\Connection;

/**
 * MediaRepository — Database operations for media.
 * 
 * @api
 */
class MediaRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {}
    
    /**
     * Find media by ID.
     */
    public function find(int $id): ?MediaFile
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM oop_media WHERE id = :id',
            ['id' => $id]
        );
        
        if (!$row) {
            return null;
        }
        
        return $this->hydrate($row);
    }
    
    /**
     * Find media by user.
     * 
     * @return array<MediaFile>
     */
    public function findByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM oop_media WHERE user_id = :user_id 
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset',
            ['user_id' => $userId, 'limit' => $limit, 'offset' => $offset]
        );
        
        return array_map([$this, 'hydrate'], $rows);
    }
    
    /**
     * Find media by type.
     * 
     * @return array<MediaFile>
     */
    public function findByType(string $type, int $limit = 50, int $offset = 0): array
    {
        $mimePattern = match($type) {
            'image' => 'image/%',
            'video' => 'video/%',
            'audio' => 'audio/%',
            default => null,
        };
        
        if (!$mimePattern) {
            return [];
        }
        
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM oop_media WHERE mime_type LIKE :pattern 
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset',
            ['pattern' => $mimePattern, 'limit' => $limit, 'offset' => $offset]
        );
        
        return array_map([$this, 'hydrate'], $rows);
    }
    
    /**
     * Search media.
     * 
     * @return array<MediaFile>
     */
    public function search(string $query, int $limit = 50, int $offset = 0): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM oop_media 
             WHERE filename LIKE :query 
                OR original_name LIKE :query 
                OR metadata LIKE :query
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset',
            ['query' => '%' . $query . '%', 'limit' => $limit, 'offset' => $offset]
        );
        
        return array_map([$this, 'hydrate'], $rows);
    }
    
    /**
     * Save media.
     */
    public function save(MediaFile $media): MediaFile
    {
        $data = [
            'filename' => $media->filename,
            'original_name' => $media->originalName,
            'path' => $media->path,
            'destination' => $media->destination,
            'mime_type' => $media->mimeType,
            'size' => $media->size,
            'extension' => $media->extension,
            'user_id' => $media->userId,
            'metadata' => json_encode($media->metadata),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($media->id === null) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->connection->insert('oop_media', $data);
            $id = (int) $this->connection->lastInsertId();
            
            return new MediaFile(
                id: $id,
                filename: $media->filename,
                originalName: $media->originalName,
                path: $media->path,
                destination: $media->destination,
                mimeType: $media->mimeType,
                size: $media->size,
                extension: $media->extension,
                userId: $media->userId,
                metadata: $media->metadata,
                createdAt: $media->createdAt,
                updatedAt: new \DateTimeImmutable(),
            );
        } else {
            $this->connection->update('oop_media', $data, ['id' => $media->id]);
            return $media;
        }
    }
    
    /**
     * Delete media.
     */
    public function delete(MediaFile $media): void
    {
        $this->connection->delete('oop_media', ['id' => $media->id]);
    }
    
    /**
     * Count media.
     */
    public function count(?string $type = null): int
    {
        if ($type) {
            $mimePattern = match($type) {
                'image' => 'image/%',
                'video' => 'video/%',
                'audio' => 'audio/%',
                default => null,
            };
            
            if ($mimePattern) {
                return (int) $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM oop_media WHERE mime_type LIKE :pattern',
                    ['pattern' => $mimePattern]
                );
            }
        }
        
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM oop_media');
    }
    
    private function hydrate(array $row): MediaFile
    {
        return new MediaFile(
            id: (int) $row['id'],
            filename: $row['filename'],
            originalName: $row['original_name'],
            path: $row['path'],
            destination: $row['destination'],
            mimeType: $row['mime_type'],
            size: (int) $row['size'],
            extension: $row['extension'],
            userId: $row['user_id'] ? (int) $row['user_id'] : null,
            metadata: json_decode($row['metadata'] ?? '[]', true),
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at']),
        );
    }
}