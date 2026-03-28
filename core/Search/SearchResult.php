<?php

declare(strict_types=1);

namespace OOPress\Search;

/**
 * SearchResult — Single search result.
 * 
 * @api
 */
class SearchResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $title,
        public readonly string $url,
        public readonly string $excerpt,
        public readonly float $score,
        public readonly array $fields = [],
        public readonly array $highlights = [],
    ) {}
    
    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: $data['type'],
            title: $data['title'],
            url: $data['url'],
            excerpt: $data['excerpt'] ?? '',
            score: $data['score'] ?? 1.0,
            fields: $data['fields'] ?? [],
            highlights: $data['highlights'] ?? [],
        );
    }
}