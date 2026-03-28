<?php

declare(strict_types=1);

namespace OOPress\Block;

/**
 * RegionDefinition — A theme region where blocks can be placed.
 * 
 * @api
 */
class RegionDefinition
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $description = '',
        public readonly bool $isAdminRegion = false,
    ) {}
    
    /**
     * Create from array.
     */
    public static function fromArray(string $id, array $data): self
    {
        return new self(
            id: $id,
            label: $data['label'] ?? $id,
            description: $data['description'] ?? '',
            isAdminRegion: $data['admin'] ?? false,
        );
    }
}
