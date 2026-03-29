<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use OOPress\GraphQL\Resolver\ContentResolver;

/**
 * ContentType — GraphQL type for content.
 * 
 * @internal
 */
class ContentType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'Content',
            'description' => 'A content item',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The unique identifier',
                ],
                'type' => [
                    'type' => Type::string(),
                    'description' => 'Content type (article, page, etc.)',
                ],
                'title' => [
                    'type' => Type::string(),
                    'description' => 'Content title',
                ],
                'slug' => [
                    'type' => Type::string(),
                    'description' => 'URL-friendly slug',
                ],
                'body' => [
                    'type' => Type::string(),
                    'description' => 'Main content body',
                ],
                'summary' => [
                    'type' => Type::string(),
                    'description' => 'Short summary/excerpt',
                ],
                'status' => [
                    'type' => $types->get('ContentStatus'),
                    'description' => 'Publication status',
                ],
                'language' => [
                    'type' => Type::string(),
                    'description' => 'Content language',
                ],
                'author_id' => [
                    'type' => Type::int(),
                    'description' => 'Author user ID',
                ],
                'author' => [
                    'type' => $types->get('User'),
                    'description' => 'Author user object',
                    'resolve' => function($content, $args, $context) {
                        $userResolver = new \OOPress\GraphQL\Resolver\UserResolver(
                            $context['container']->get(\Doctrine\DBAL\Connection::class),
                            $context['container']->get(\OOPress\Security\UserProvider::class),
                            $context['container']->get(\OOPress\Security\PasswordHasher::class),
                            $context['container']->get(\OOPress\Security\AuthorizationManager::class)
                        );
                        return $userResolver->resolveUser(null, ['id' => $content->author_id], $context);
                    },
                ],
                'translations' => [
                    'type' => Type::listOf($types->get('ContentTranslation')),
                    'description' => 'Available translations',
                ],
                'fields' => [
                    'type' => Type::listOf($types->get('FieldValue')),
                    'description' => 'Custom field values',
                ],
                'created_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Creation timestamp',
                ],
                'updated_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Last update timestamp',
                ],
                'published_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Publication timestamp',
                ],
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Public URL',
                    'resolve' => function($content) {
                        return '/content/' . $content->slug;
                    },
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}