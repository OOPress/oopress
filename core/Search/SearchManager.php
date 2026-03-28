<?php

declare(strict_types=1);

namespace OOPress\Search;

use OOPress\Event\HookDispatcher;

/**
 * SearchManager — Manages search backends and indexing.
 * 
 * @api
 */
class SearchManager
{
    private SearchInterface $backend;
    private array $indexers = [];
    
    public function __construct(
        private readonly HookDispatcher $hookDispatcher,
        array $config = [],
    ) {
        $this->initializeBackend($config);
    }
    
    /**
     * Initialize search backend based on configuration.
     */
    private function initializeBackend(array $config): void
    {
        $backendType = $config['backend'] ?? 'database';
        
        switch ($backendType) {
            case 'database':
                // Will be injected via container
                break;
            case 'elasticsearch':
                // For future implementation
                throw new \RuntimeException('Elasticsearch backend not yet implemented');
            case 'algolia':
                // For future implementation (requires external service)
                throw new \RuntimeException('Algolia backend not yet implemented');
            default:
                throw new \RuntimeException(sprintf('Unknown search backend: %s', $backendType));
        }
    }
    
    /**
     * Set search backend.
     */
    public function setBackend(SearchInterface $backend): void
    {
        $this->backend = $backend;
    }
    
    /**
     * Get search backend.
     */
    public function getBackend(): SearchInterface
    {
        return $this->backend;
    }
    
    /**
     * Register an indexer.
     */
    public function registerIndexer(string $type, callable $indexer): void
    {
        $this->indexers[$type] = $indexer;
    }
    
    /**
     * Index a document.
     */
    public function index(IndexableInterface $document): void
    {
        $this->backend->index($document);
    }
    
    /**
     * Remove a document.
     */
    public function remove(IndexableInterface $document): void
    {
        $this->backend->remove($document);
    }
    
    /**
     * Search.
     */
    public function search(SearchQuery $query): SearchResultCollection
    {
        return $this->backend->search($query);
    }
    
    /**
     * Rebuild the entire index.
     */
    public function rebuild(): void
    {
        $this->backend->rebuild();
    }
    
    /**
     * Clear the index.
     */
    public function clear(): void
    {
        $this->backend->clear();
    }
    
    /**
     * Get index statistics.
     */
    public function getStats(): array
    {
        return $this->backend->getStats();
    }
    
    /**
     * Check if search is available.
     */
    public function isAvailable(): bool
    {
        return $this->backend !== null && $this->backend->isAvailable();
    }
}