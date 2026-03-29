<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Event;

use GraphQL\Type\Schema;
use OOPress\GraphQL\Type\Types;
use OOPress\Event\Event;

/**
 * SchemaBuildEvent — Dispatched when building GraphQL schema.
 * 
 * Modules can listen to this event to add custom types and fields.
 * 
 * @api
 */
class SchemaBuildEvent extends Event
{
    public function __construct(
        private readonly Schema $schema,
        private readonly Types $types,
    ) {
        parent::__construct();
    }
    
    public function getSchema(): Schema
    {
        return $this->schema;
    }
    
    public function getTypes(): Types
    {
        return $this->types;
    }
}