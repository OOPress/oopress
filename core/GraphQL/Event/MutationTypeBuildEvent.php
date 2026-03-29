<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Event;

use OOPress\Event\Event;
use OOPress\GraphQL\Type\Types;

/**
 * MutationTypeBuildEvent — Dispatched when building GraphQL Mutation type.
 * 
 * Modules can listen to this event to add custom mutation fields.
 * 
 * @api
 */
class MutationTypeBuildEvent extends Event
{
    private array $config;
    private Types $types;
    
    public function __construct(array &$config, Types $types)
    {
        parent::__construct();
        $this->config = &$config;
        $this->types = $types;
    }
    
    /**
     * Add a custom mutation field.
     */
    public function addField(string $name, array $fieldConfig): void
    {
        $this->config['fields'][$name] = $fieldConfig;
    }
    
    /**
     * Get the field configuration array (for modification).
     */
    public function &getFields(): array
    {
        return $this->config['fields'];
    }
    
    /**
     * Get the types registry.
     */
    public function getTypes(): Types
    {
        return $this->types;
    }
}