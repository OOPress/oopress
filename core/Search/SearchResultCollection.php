<?php

declare(strict_types=1);

namespace OOPress\Search;

/**
 * SearchResultCollection — Collection of search results.
 * 
 * @api
 */
class SearchResultCollection implements \IteratorAggregate, \Countable
{
    private array $results = [];
    private int $total = 0;
    private array $facets = [];
    private array $suggestions = [];
    
    public function __construct(
        array $results = [],
        int $total = 0,
        array $facets = [],
        array $suggestions = [],
    ) {
        $this->results = $results;
        $this->total = $total;
        $this->facets = $facets;
        $this->suggestions = $suggestions;
    }
    
    public function addResult(SearchResult $result): void
    {
        $this->results[] = $result;
    }
    
    public function getResults(): array
    {
        return $this->results;
    }
    
    public function getTotal(): int
    {
        return $this->total;
    }
    
    public function getFacets(): array
    {
        return $this->facets;
    }
    
    public function getSuggestions(): array
    {
        return $this->suggestions;
    }
    
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->results);
    }
    
    public function count(): int
    {
        return count($this->results);
    }
    
    public function isEmpty(): bool
    {
        return empty($this->results);
    }
    
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'results' => array_map(fn($r) => [
                'id' => $r->id,
                'type' => $r->type,
                'title' => $r->title,
                'url' => $r->url,
                'excerpt' => $r->excerpt,
                'score' => $r->score,
                'fields' => $r->fields,
                'highlights' => $r->highlights,
            ], $this->results),
            'facets' => $this->facets,
            'suggestions' => $this->suggestions,
        ];
    }
}