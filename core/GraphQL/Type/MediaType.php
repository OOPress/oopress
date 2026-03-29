<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * MediaType — GraphQL type for media files.
 * 
 * @internal
 */
class MediaType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'Media',
            'description' => 'A media file',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The unique identifier',
                ],
                'filename' => [
                    'type' => Type::string(),
                    'description' => 'Stored filename',
                ],
                'original_name' => [
                    'type' => Type::string(),
                    'description' => 'Original uploaded filename',
                ],
                'mime_type' => [
                    'type' => Type::string(),
                    'description' => 'MIME type',
                ],
                'size' => [
                    'type' => Type::int(),
                    'description' => 'File size in bytes',
                ],
                'formatted_size' => [
                    'type' => Type::string(),
                    'description' => 'Human-readable file size',
                    'resolve' => function($media) {
                        return $media->getFormattedSize();
                    },
                ],
                'extension' => [
                    'type' => Type::string(),
                    'description' => 'File extension',
                ],
                'destination' => [
                    'type' => Type::string(),
                    'description' => 'Storage destination (public/private)',
                ],
                'is_image' => [
                    'type' => Type::boolean(),
                    'description' => 'Whether file is an image',
                    'resolve' => function($media) {
                        return $media->isImage();
                    },
                ],
                'url' => [
                    'type' => Type::string(),
                    'description' => 'Public URL',
                    'resolve' => function($media, $args, $context) {
                        $mediaManager = $context['container']->get(\OOPress\Media\MediaManager::class);
                        return $mediaManager->getUrl($media);
                    },
                ],
                'thumbnail' => [
                    'type' => Type::string(),
                    'description' => 'Thumbnail URL',
                    'resolve' => function($media, $args, $context) {
                        $mediaManager = $context['container']->get(\OOPress\Media\MediaManager::class);
                        return $mediaManager->getUrl($media, 'thumbnail');
                    },
                ],
                'medium' => [
                    'type' => Type::string(),
                    'description' => 'Medium size URL',
                    'resolve' => function($media, $args, $context) {
                        $mediaManager = $context['container']->get(\OOPress\Media\MediaManager::class);
                        return $mediaManager->getUrl($media, 'medium');
                    },
                ],
                'large' => [
                    'type' => Type::string(),
                    'description' => 'Large size URL',
                    'resolve' => function($media, $args, $context) {
                        $mediaManager = $context['container']->get(\OOPress\Media\MediaManager::class);
                        return $mediaManager->getUrl($media, 'large');
                    },
                ],
                'user_id' => [
                    'type' => Type::int(),
                    'description' => 'Uploader user ID',
                ],
                'uploader' => [
                    'type' => $types->get('User'),
                    'description' => 'Uploader user object',
                    'resolve' => function($media, $args, $context) {
                        if (!$media->user_id) return null;
                        $userResolver = new \OOPress\GraphQL\Resolver\UserResolver(
                            $context['container']->get(\Doctrine\DBAL\Connection::class),
                            $context['container']->get(\OOPress\Security\UserProvider::class),
                            $context['container']->get(\OOPress\Security\PasswordHasher::class),
                            $context['container']->get(\OOPress\Security\AuthorizationManager::class)
                        );
                        return $userResolver->resolveUser(null, ['id' => $media->user_id], $context);
                    },
                ],
                'metadata' => [
                    'type' => Type::string(),
                    'description' => 'File metadata (JSON)',
                ],
                'created_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Upload timestamp',
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