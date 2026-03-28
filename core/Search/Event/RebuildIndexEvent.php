<?php

declare(strict_types=1);

namespace OOPress\Search\Event;

use OOPress\Search\SearchInterface;
use OOPress\Event\Event;

/**
 * RebuildIndexEvent — Dispatched when rebuilding the search index.
 * 
 * Modules should listen to this event to index their content.
 * 
 * @api
 */
class RebuildIndexEvent extends Event
{
    public function __construct(
        private readonly SearchInterface $search,
    ) {
        parent::__construct();
    }
    
    public function getSearch(): SearchInterface
    {
        return $this->search;
    }
}