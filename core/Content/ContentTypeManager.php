<?php

declare(strict_types=1);

namespace OOPress\Content;

use Doctrine\DBAL\Connection;
use OOPress\Event\HookDispatcher;
use OOPress\Path\PathResolver;

/**
 * ContentTypeManager — Manages content type definitions.
 * 
 * Content types can be defined by modules via YAML files or programmatically.
 * 
 * @api
 */
class ContentTypeManager
{
    /**
     * @var array<string, ContentType>
     */
    private array $contentTypes = [];
    
    /**
     * @var array<string, string>
     */
    private array $errors = [];
    
    public function __construct(
        private readonly Connection $connection,
        private readonly PathResolver $pathResolver,
        private readonly HookDispatcher $hookDispatcher,
    ) {}
    
    /**
     * Discover content types from modules.
     */
    public function discoverContentTypes(): void
    {
        $modulesPath = $this->pathResolver->getModulesPath();
        
        if (!is_dir($modulesPath)) {
            return;
        }
        
        $iterator = new \DirectoryIterator($modulesPath);
        
        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isDir()) {
                continue;
            }
            
            $contentTypesFile = $item->getPathname() . '/content_types.yaml';
            if (file_exists($contentTypesFile)) {
                $this->loadContentTypesFromFile($contentTypesFile);
            }
        }
        
        // Allow modules to register content types programmatically
        $event = new Event\ContentTypesEvent($this);
        $this->hookDispatcher->dispatch($event, 'content_type.register');
    }
    
    /**
     * Load content types from a YAML file.
     */
    private function loadContentTypesFromFile(string $filePath): void
    {
        $yaml = file_get_contents($filePath);
        $data = \Symfony\Component\Yaml\Yaml::parse($yaml);
        
        if (!is_array($data)) {
            return;
        }
        
        foreach ($data as $id => $definition) {
            try {
                $this->registerContentType(ContentType::fromArray($id, $definition));
            } catch (\Exception $e) {
                $this->errors[] = sprintf(
                    'Failed to load content type %s from %s: %s',
                    $id,
                    $filePath,
                    $e->getMessage()
                );
            }
        }
    }
    
    /**
     * Register a content type.
     */
    public function registerContentType(ContentType $contentType): void
    {
        $this->contentTypes[$contentType->id] = $contentType;
    }
    
    /**
     * Get a content type by ID.
     */
    public function getContentType(string $id): ?ContentType
    {
        return $this->contentTypes[$id] ?? null;
    }
    
    /**
     * Get all content types.
     * 
     * @return array<string, ContentType>
     */
    public function getAll(): array
    {
        return $this->contentTypes;
    }
    
    /**
     * Check if a content type exists.
     */
    public function hasContentType(string $id): bool
    {
        return isset($this->contentTypes[$id]);
    }
    
    /**
     * Get all errors from discovery.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Create the database table for a content type.
     * 
     * This is called when a content type is installed.
     */
    public function createContentTypeTable(ContentType $contentType): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $tableName = 'oop_content_' . $contentType->id;
        
        if ($schemaManager->tablesExist([$tableName])) {
            return;
        }
        
        // Base table for this content type
        $this->connection->executeStatement(sprintf(
            'CREATE TABLE %s (
                id INT PRIMARY KEY AUTO_INCREMENT,
                content_id INT NOT NULL,
                language VARCHAR(10) NOT NULL,
                is_default BOOLEAN DEFAULT FALSE,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uk_content_language (content_id, language),
                FOREIGN KEY (content_id) REFERENCES oop_content(id) ON DELETE CASCADE
            )',
            $tableName
        ));
    }
}
