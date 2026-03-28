<?php

declare(strict_types=1);

namespace OOPress\Asset;

/**
 * AssetDefinition — Defines an asset (CSS, JS, font).
 * 
 * @api
 */
class AssetDefinition
{
    public function __construct(
        public readonly string $id,
        public readonly string $sourceId,
        public readonly string $type,
        public readonly string $path,
        public readonly int $weight = 0,
        public readonly ?string $cdn = null,
        public readonly string $media = 'all',
        public readonly bool $defer = false,
        public readonly bool $async = false,
        public readonly array $dependencies = [],
        public readonly bool $compile = true,
        public readonly ?string $compiledPath = null,
    ) {}
    
    /**
     * Create from array.
     */
    public static function fromArray(string $id, array $data, string $type, string $sourceId): self
    {
        return new self(
            id: $id,
            sourceId: $sourceId,
            type: $type,
            path: $data['path'] ?? '',
            weight: $data['weight'] ?? 0,
            cdn: $data['cdn'] ?? null,
            media: $data['media'] ?? 'all',
            defer: $data['defer'] ?? false,
            async: $data['async'] ?? false,
            dependencies: $data['dependencies'] ?? [],
            compile: $data['compile'] ?? true,
            compiledPath: $data['compiled_path'] ?? null,
        );
    }
    
    /**
     * Check if asset should use CDN.
     */
    public function useCdn(bool $selfHostDefault = true): bool
    {
        if ($this->cdn === null) {
            return false;
        }
        
        return !$selfHostDefault;
    }
}