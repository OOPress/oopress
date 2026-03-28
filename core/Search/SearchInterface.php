<?php

declare(strict_types=1);

namespace OOPress\Search;

/**
 * SearchInterface — Contract for search backends.
 * 
 * @api
 */
interface SearchInterface
{
    /**
     * Index a document.
     */
    public function index(IndexableInterface $document): void;
    
    /**
     * Remove a document from index.
     */
    public function remove(IndexableInterface $document): void;
    
    /**
     * Search for documents.
     * 
     * @return SearchResultCollection
     */
    public function search(SearchQuery $query): SearchResultCollection;
    
    /**
     * Rebuild the entire index.
     */
    public function rebuild(): void;
    
    /**
     * Clear the index.
     */
    public function clear(): void;
    
    /**
     * Get index statistics.
     */
    public function getStats(): array;
    
    /**
     * Check if search backend is available.
     */
    public function isAvailable(): bool;
}