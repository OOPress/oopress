<?php

declare(strict_types=1);

namespace OOPress\Content\Revision;

/**
 * Revision — A snapshot of content at a point in time.
 * 
 * @api
 */
class Revision
{
    public function __construct(
        public readonly int $id,
        public readonly int $contentId,
        public readonly int $revisionNumber,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $body,
        public readonly array $fields,
        public readonly int $authorId,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
    
    /**
     * Get a field value from the revision snapshot.
     */
    public function getField(string $name, mixed $default = null): mixed
    {
        return $this->fields[$name] ?? $default;
    }
}
