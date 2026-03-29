<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * ContentTranslationType — GraphQL type for content translations.
 * 
 * @internal
 */
class ContentTranslationType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'ContentTranslation',
            'description' => 'A content translation',
            'fields' => [
                'id' => [
                    'type' => Type::id(),
                    'description' => 'Translation unique identifier',
                ],
                'content_id' => [
                    'type' => Type::int(),
                    'description' => 'Parent content ID',
                ],
                'language' => [
                    'type' => Type::string(),
                    'description' => 'Language code (en, fr, de, etc.)',
                ],
                'title' => [
                    'type' => Type::string(),
                    'description' => 'Translated title',
                ],
                'slug' => [
                    'type' => Type::string(),
                    'description' => 'URL-friendly slug for this language',
                ],
                'body' => [
                    'type' => Type::string(),
                    'description' => 'Translated body content',
                ],
                'summary' => [
                    'type' => Type::string(),
                    'description' => 'Translated summary',
                ],
                'is_default' => [
                    'type' => Type::boolean(),
                    'description' => 'Whether this is the default translation',
                ],
                'fields' => [
                    'type' => Type::string(),
                    'description' => 'JSON string of translated field values',
                ],
                'created_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Creation timestamp',
                ],
                'updated_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Last update timestamp',
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}