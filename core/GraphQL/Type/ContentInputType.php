<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * ContentInputType — GraphQL input type for creating/updating content.
 * 
 * @internal
 */
class ContentInputType extends InputObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'ContentInput',
            'description' => 'Input for creating or updating content',
            'fields' => [
                'content_type' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Content type (article, page, etc.)',
                ],
                'title' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Content title',
                ],
                'slug' => [
                    'type' => Type::string(),
                    'description' => 'URL-friendly slug (auto-generated if not provided)',
                ],
                'body' => [
                    'type' => Type::string(),
                    'description' => 'Main content body',
                ],
                'summary' => [
                    'type' => Type::string(),
                    'description' => 'Short summary/excerpt',
                ],
                'language' => [
                    'type' => Type::string(),
                    'description' => 'Content language (default: en)',
                    'defaultValue' => 'en',
                ],
                'status' => [
                    'type' => $types->get('ContentStatus'),
                    'description' => 'Publication status',
                    'defaultValue' => 'draft',
                ],
                'fields' => [
                    'type' => Type::string(),
                    'description' => 'JSON string of custom field values',
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}