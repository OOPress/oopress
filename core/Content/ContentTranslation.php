<?php

declare(strict_types=1);

namespace OOPress\Content;

/**
 * ContentTranslation — A translation of a content entity.
 * 
 * @api
 */
class ContentTranslation
{
    /**
     * @param array<string, mixed> $fields Extended field values
     */
    public function __construct(
        public readonly int $id,
        public readonly int $contentId,
        public readonly string $language,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $body = null,
        public readonly ?string $summary = null,
        public readonly bool $isDefault = false,
        public readonly array $fields = [],
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
    
    /**
     * Get a field value.
     */
    public function getField(string $name, mixed $default = null): mixed
    {
        return $this->fields[$name] ?? $default;
    }
    
    /**
     * Check if a field exists.
     */
    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }
    
    /**
     * Get the URL slug.
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
    
    /**
     * Get the title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
