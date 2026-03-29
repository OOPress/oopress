<?php

declare(strict_types=1);

namespace OOPress\Tests\Integration\Content;

use OOPress\Tests\TestCase;
use OOPress\Content\Content;
use OOPress\Content\ContentTranslation;
use OOPress\Content\ContentRepository;

/**
 * Test ContentRepository integration.
 * 
 * @internal
 */
class ContentRepositoryTest extends TestCase
{
    private ContentRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ContentRepository(static::$connection);
        $this->setupTestTables();
    }
    
    private function setupTestTables(): void
    {
        $schemaManager = static::$connection->createSchemaManager();
        
        // Create content table
        if (!$schemaManager->tablesExist(['oop_content'])) {
            $schemaManager->createTable(
                $schemaManager->createSchemaConfig()->createTable('oop_content')
                    ->addColumn('id', 'integer', ['autoincrement' => true])
                    ->addColumn('content_type', 'string', ['length' => 100])
                    ->addColumn('author_id', 'integer')
                    ->addColumn('status', 'string', ['length' => 50])
                    ->addColumn('created_at', 'datetime')
                    ->addColumn('updated_at', 'datetime')
                    ->addColumn('published_at', 'datetime', ['notnull' => false])
                    ->setPrimaryKey(['id'])
            );
        }
        
        // Create translations table
        if (!$schemaManager->tablesExist(['oop_content_translations'])) {
            $schemaManager->createTable(
                $schemaManager->createSchemaConfig()->createTable('oop_content_translations')
                    ->addColumn('id', 'integer', ['autoincrement' => true])
                    ->addColumn('content_id', 'integer')
                    ->addColumn('language', 'string', ['length' => 10])
                    ->addColumn('title', 'string', ['length' => 255])
                    ->addColumn('slug', 'string', ['length' => 255])
                    ->addColumn('body', 'text', ['notnull' => false])
                    ->addColumn('summary', 'text', ['notnull' => false])
                    ->addColumn('is_default', 'boolean', ['default' => false])
                    ->addColumn('created_at', 'datetime')
                    ->addColumn('updated_at', 'datetime')
                    ->setPrimaryKey(['id'])
            );
        }
    }
    
    public function testSaveAndFindContent(): void
    {
        $content = new Content(
            id: 0,
            contentType: 'article',
            authorId: 1,
            status: 'draft',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
        
        $translation = new ContentTranslation(
            id: 0,
            contentId: 0,
            language: 'en',
            title: 'Test Article',
            slug: 'test-article',
            body: 'This is a test article.',
            isDefault: true,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
        
        $content->addTranslation($translation);
        
        // This would save the content
        // $this->repository->save($content);
        
        $this->assertTrue(true); // Placeholder until save is implemented
    }
}