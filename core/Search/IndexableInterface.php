<?php

declare(strict_types=1);

namespace OOPress\Search;

/**
 * IndexableInterface — Contract for objects that can be indexed.
 * 
 * @api
 */
interface IndexableInterface
{
    /**
     * Get unique identifier for the document.
     */
    public function getSearchId(): string;
    
    /**
     * Get document type (e.g., 'content', 'user', 'media').
     */
    public function getSearchType(): string;
    
    /**
     * Get the title/name of the document.
     */
    public function getSearchTitle(): string;
    
    /**
     * Get the main content/body for indexing.
     */
    public function getSearchContent(): string;
    
    /**
     * Get the URL to view this document.
     */
    public function getSearchUrl(): string;
    
    /**
     * Get additional fields for indexing.
     * 
     * @return array<string, mixed>
     */
    public function getSearchFields(): array;
    
    /**
     * Get access control data (roles, user IDs).
     */
    public function getSearchAccess(): array;
}