<?php

declare(strict_types=1);

namespace OOPress\Content\Event;

use OOPress\Content\ContentTypeManager;
use OOPress\Event\Event;

/**
 * ContentTypesEvent — Dispatched when content types should be registered.
 * 
 * Modules can listen to this event to register content types programmatically.
 * 
 * @api
 */
class ContentTypesEvent extends Event
{
    public function __construct(
        private readonly ContentTypeManager $manager,
    ) {
        parent::__construct();
    }
    
    public function getManager(): ContentTypeManager
    {
        return $this->manager;
    }
}
