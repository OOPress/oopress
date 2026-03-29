<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\EnumType;

/**
 * Types — Registry of GraphQL types.
 * 
 * @internal
 */
class Types
{
    private array $types = [];
    
    public function __construct()
    {
        $this->registerCoreTypes();
    }
    
    /**
     * Register core GraphQL types.
     */
    private function registerCoreTypes(): void
    {
        // Scalar types
        $this->types['ID'] = Type::id();
        $this->types['String'] = Type::string();
        $this->types['Int'] = Type::int();
        $this->types['Float'] = Type::float();
        $this->types['Boolean'] = Type::boolean();
        $this->types['DateTime'] = new DateTimeType();
        
        // Content types
        $this->types['Content'] = new ContentType($this);
        $this->types['ContentInput'] = new ContentInputType($this);
        $this->types['ContentTranslation'] = new ContentTranslationType($this);
        $this->types['FieldValue'] = new FieldValueType($this);
        
        // User types
        $this->types['User'] = new UserType($this);
        $this->types['UserInput'] = new UserInputType($this);
        
        // Block types
        $this->types['Block'] = new BlockType($this);
        $this->types['Region'] = new RegionType($this);
        
        // Media types
        $this->types['Media'] = new MediaType($this);
        
        // Query types
        $this->types['QueryResult'] = new QueryResultType($this);
        
        // Enum types
        $this->types['ContentStatus'] = new EnumType([
            'name' => 'ContentStatus',
            'values' => [
                'DRAFT' => ['value' => 'draft'],
                'PUBLISHED' => ['value' => 'published'],
                'ARCHIVED' => ['value' => 'archived'],
            ],
        ]);
        
        $this->types['SortOrder'] = new EnumType([
            'name' => 'SortOrder',
            'values' => [
                'ASC' => ['value' => 'ASC'],
                'DESC' => ['value' => 'DESC'],
            ],
        ]);
    }
    
    /**
     * Get a type by name.
     */
    public function get(string $name): ?Type
    {
        return $this->types[$name] ?? null;
    }
    
    /**
     * Register a custom type.
     */
    public function register(string $name, Type $type): void
    {
        $this->types[$name] = $type;
    }
    
    /**
     * Check if a type exists.
     */
    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }
}