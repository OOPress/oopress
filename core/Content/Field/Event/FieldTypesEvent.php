<?php

declare(strict_types=1);

namespace OOPress\Content\Event;

use OOPress\Content\Field\FieldManager;
use OOPress\Event\Event;

/**
 * FieldTypesEvent — Dispatched when field types should be registered.
 * 
 * Modules can listen to this event to register custom field types.
 * 
 * @api
 */
class FieldTypesEvent extends Event
{
    public function __construct(
        private readonly FieldManager $fieldManager,
    ) {
        parent::__construct();
    }
    
    public function getFieldManager(): FieldManager
    {
        return $this->fieldManager;
    }
}
