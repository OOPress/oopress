<?php

declare(strict_types=1);

namespace OOPress\Update;

/**
 * UpdateInfo — Information about an available update.
 * 
 * @api
 */
class UpdateInfo
{
    public function __construct(
        public readonly string $version,
        public readonly string $type, // 'core' or 'module'
        public readonly ?string $moduleId,
        public readonly string $releaseDate,
        public readonly string $releaseNotes,
        public readonly string $stability, // 'stable', 'beta', 'alpha'
        public readonly bool $securityUpdate,
    ) {}
    
    /**
     * Create from array.
     */
    public static function fromArray(array $data, string $type, ?string $moduleId = null): self
    {
        return new self(
            version: $data['version'],
            type: $type,
            moduleId: $moduleId,
            releaseDate: $data['release_date'] ?? '',
            releaseNotes: $data['release_notes'] ?? '',
            stability: $data['stability'] ?? 'stable',
            securityUpdate: $data['security_update'] ?? false,
        );
    }
    
    /**
     * Get human-readable label.
     */
    public function getLabel(): string
    {
        if ($this->type === 'core') {
            return sprintf('OOPress %s', $this->version);
        }
        
        return sprintf('%s %s', $this->moduleId, $this->version);
    }
    
    /**
     * Check if update is urgent (security update).
     */
    public function isUrgent(): bool
    {
        return $this->securityUpdate;
    }
}