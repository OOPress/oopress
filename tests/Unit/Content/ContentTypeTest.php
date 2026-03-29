<?php

declare(strict_types=1);

namespace OOPress\Tests\Unit\Content;

use OOPress\Tests\TestCase;
use OOPress\Content\ContentType;
use OOPress\Content\Field\FieldDefinition;

/**
 * Test ContentType functionality.
 * 
 * @internal
 */
class ContentTypeTest extends TestCase
{
    public function testCreateContentType(): void
    {
        $contentType = new ContentType(
            id: 'article',
            label: 'Article',
            description: 'Blog article content type'
        );
        
        $this->assertEquals('article', $contentType->id);
        $this->assertEquals('Article', $contentType->label);
        $this->assertEquals('Blog article content type', $contentType->description);
    }
    
    public function testContentTypeWithFields(): void
    {
        $field = new FieldDefinition(
            name: 'subtitle',
            type: 'text',
            label: 'Subtitle',
            required: false
        );
        
        $contentType = new ContentType(
            id: 'article',
            label: 'Article',
            fields: [$field]
        );
        
        $this->assertTrue($contentType->hasField('subtitle'));
        $this->assertNotNull($contentType->getField('subtitle'));
        $this->assertNull($contentType->getField('nonexistent'));
    }
    
    public function testSupportsRevisions(): void
    {
        $contentType = new ContentType(
            id: 'article',
            label: 'Article',
            settings: ['revisions' => true]
        );
        
        $this->assertTrue($contentType->supportsRevisions());
        
        $contentType = new ContentType(
            id: 'page',
            label: 'Page',
            settings: ['revisions' => false]
        );
        
        $this->assertFalse($contentType->supportsRevisions());
        
        // Default should be true
        $contentType = new ContentType(
            id: 'basic',
            label: 'Basic'
        );
        
        $this->assertTrue($contentType->supportsRevisions());
    }
    
    public function testGetDefaultLanguage(): void
    {
        $contentType = new ContentType(
            id: 'article',
            label: 'Article',
            settings: ['default_language' => 'fr']
        );
        
        $this->assertEquals('fr', $contentType->getDefaultLanguage());
        
        // Default should be 'en'
        $contentType = new ContentType(
            id: 'article',
            label: 'Article'
        );
        
        $this->assertEquals('en', $contentType->getDefaultLanguage());
    }
    
    public function testIsTranslationRequired(): void
    {
        $contentType = new ContentType(
            id: 'article',
            label: 'Article',
            settings: ['translation_required' => true]
        );
        
        $this->assertTrue($contentType->isTranslationRequired());
        
        $contentType = new ContentType(
            id: 'article',
            label: 'Article'
        );
        
        $this->assertFalse($contentType->isTranslationRequired());
    }
    
    public function testFromArray(): void
    {
        $data = [
            'label' => 'Article',
            'description' => 'Blog posts',
            'settings' => ['revisions' => true],
            'fields' => [
                'subtitle' => [
                    'type' => 'text',
                    'label' => 'Subtitle',
                ],
            ],
        ];
        
        $contentType = ContentType::fromArray('article', $data);
        
        $this->assertEquals('article', $contentType->id);
        $this->assertEquals('Article', $contentType->label);
        $this->assertEquals('Blog posts', $contentType->description);
        $this->assertTrue($contentType->hasField('subtitle'));
    }
    
    public function testToArray(): void
    {
        $field = new FieldDefinition(
            name: 'subtitle',
            type: 'text',
            label: 'Subtitle'
        );
        
        $contentType = new ContentType(
            id: 'article',
            label: 'Article',
            description: 'Blog posts',
            settings: ['revisions' => true],
            fields: [$field]
        );
        
        $array = $contentType->toArray();
        
        $this->assertEquals('Article', $array['label']);
        $this->assertEquals('Blog posts', $array['description']);
        $this->assertTrue($array['settings']['revisions']);
        $this->assertCount(1, $array['fields']);
    }
}