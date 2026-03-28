<?php

declare(strict_types=1);

namespace OOPress\Block;

/**
 * BlockAssignment — A block assigned to a region with settings.
 * 
 * @api
 */
class BlockAssignment
{
    public function __construct(
        public readonly int $id,
        public readonly string $blockId,
        public readonly string $region,
        public readonly int $weight,
        public readonly array $settings,
        public readonly bool $status,
        public readonly \DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}
    
    /**
     * Create from database row.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            blockId: $data['block_id'],
            region: $data['region'],
            weight: (int) $data['weight'],
            settings: json_decode($data['settings'] ?? '[]', true),
            status: (bool) ($data['status'] ?? true),
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }
    
    /**
     * Get a setting value.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}
