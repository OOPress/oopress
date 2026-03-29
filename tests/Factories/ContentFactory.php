<?php

declare(strict_types=1);

namespace OOPress\Tests\Factories;

use OOPress\Content\Content;
use OOPress\Content\ContentTranslation;

/**
 * Factory for creating test content.
 * 
 * @internal
 */
class ContentFactory
{
    /**
     * Create content for testing.
     */
    public function create(array $attributes = []): Content
    {
        $defaults = [
            'id' => 0,
            'contentType' => 'article',
            'authorId' => 1,
            'status' => 'draft',
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
            'publishedAt' => null,
        ];
        
        $data = array_merge($defaults, $attributes);
        
        $content = new Content(
            id: $data['id'],
            contentType: $data['contentType'],
            authorId: $data['authorId'],
            status: $data['status'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            publishedAt: $data['publishedAt']
        );
        
        // Add default translation
        $translation = new ContentTranslation(
            id: 0,
            contentId: 0,
            language: 'en',
            title: 'Test Content',
            slug: 'test-content',
            body: 'This is test content body.',
            isDefault: true,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
        
        $content->addTranslation($translation);
        
        return $content;
    }
    
    /**
     * Create published content.
     */
    public function createPublished(array $attributes = []): Content
    {
        return $this->create(array_merge([
            'status' => 'published',
            'publishedAt' => new \DateTimeImmutable(),
        ], $attributes));
    }
    
    /**
     * Create content with multiple translations.
     */
    public function createMultilingual(array $attributes = []): Content
    {
        $content = $this->create($attributes);
        
        // Add French translation
        $frenchTranslation = new ContentTranslation(
            id: 0,
            contentId: 0,
            language: 'fr',
            title: 'Contenu de Test',
            slug: 'contenu-de-test',
            body: 'Ceci est un contenu de test.',
            isDefault: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
        
        $content->addTranslation($frenchTranslation);
        
        return $content;
    }
}