<?php

declare(strict_types=1);

namespace OOPress\Block;

/**
 * BlockDefinition — Metadata about a block provided by a module.
 * 
 * @api
 */
class BlockDefinition
{
    /**
     * @param string $id Block machine name
     * @param string $label Human-readable label
     * @param string $module Module that provides this block
     * @param class-string<BlockInterface> $class Block class
     * @param array<string, mixed> $defaultSettings Default settings
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $module,
        public readonly string $class,
        public readonly string $description = '',
        public readonly string $category = 'General',
        public readonly array $defaultSettings = [],
        public readonly bool $cacheable = true,
        public readonly array $cacheTags = [],
        public readonly array $cacheContexts = ['user.roles'],
    ) {}
    
    /**
     * Create from array.
     */
    public static function fromArray(string $id, array $data): self
    {
        return new self(
            id: $id,
            label: $data['label'] ?? $id,
            module: $data['module'] ?? '',
            class: $data['class'] ?? '',
            description: $data['description'] ?? '',
            category: $data['category'] ?? 'General',
            defaultSettings: $data['settings'] ?? [],
            cacheable: $data['cacheable'] ?? true,
            cacheTags: $data['cache_tags'] ?? [],
            cacheContexts: $data['cache_contexts'] ?? ['user.roles'],
        );
    }
    
    /**
     * Create an instance of the block.
     */
    public function createInstance(): BlockInterface
    {
        if (!class_exists($this->class)) {
            throw new \RuntimeException(sprintf('Block class not found: %s', $this->class));
        }
        
        $instance = new $this->class();
        
        if (!$instance instanceof BlockInterface) {
            throw new \RuntimeException(sprintf(
                'Block class %s must implement BlockInterface',
                $this->class
            ));
        }
        
        return $instance;
    }
}
