<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * FieldValueType — GraphQL type for custom field values.
 * 
 * @internal
 */
class FieldValueType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'FieldValue',
            'description' => 'A custom field value',
            'fields' => [
                'name' => [
                    'type' => Type::string(),
                    'description' => 'Field name',
                ],
                'type' => [
                    'type' => Type::string(),
                    'description' => 'Field type (text, number, image, etc.)',
                ],
                'label' => [
                    'type' => Type::string(),
                    'description' => 'Human-readable label',
                ],
                'value' => [
                    'type' => Type::string(),
                    'description' => 'Field value',
                ],
                'formatted' => [
                    'type' => Type::string(),
                    'description' => 'Formatted value for display',
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}