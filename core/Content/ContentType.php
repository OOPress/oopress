<?php

declare(strict_types=1);

namespace OOPress\Content;

/**
 * ContentType — Defines a content type (e.g., "article", "page", "blog_post").
 * 
 * @api
 */
class ContentType
{
    /**
     * @param string $id Machine name (e.g., "article")
     * @param string $label Human-readable label (e.g., "Article")
     * @param array<string, mixed> $settings Additional settings
     * @param array<Field\FieldDefinition> $fields Field definitions for this type
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $description = '',
        public readonly array $settings = [],
        public readonly array $fields = [],
    ) {}
    
    /**
     * Get a field definition by name.
     */
    public function getField(string $name): ?Field\FieldDefinition
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }
        
        return null;
    }
    
    /**
     * Check if the content type has a specific field.
     */
    public function hasField(string $name): bool
    {
        return $this->getField($name) !== null;
    }
    
    /**
     * Check if this content type supports revisions.
     */
    public function supportsRevisions(): bool
    {
        return $this->settings['revisions'] ?? true;
    }
    
    /**
     * Get the default language for this content type.
     */
    public function getDefaultLanguage(): string
    {
        return $this->settings['default_language'] ?? 'en';
    }
    
    /**
     * Check if translations are required.
     */
    public function isTranslationRequired(): bool
    {
        return $this->settings['translation_required'] ?? false;
    }
    
    /**
     * Create from array (for storage/configuration).
     */
    public static function fromArray(string $id, array $data): self
    {
        $fields = [];
        foreach ($data['fields'] ?? [] as $fieldName => $fieldData) {
            $fields[] = Field\FieldDefinition::fromArray($fieldName, $fieldData);
        }
        
        return new self(
            id: $id,
            label: $data['label'] ?? $id,
            description: $data['description'] ?? '',
            settings: $data['settings'] ?? [],
            fields: $fields,
        );
    }
    
    /**
     * Convert to array for storage.
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'description' => $this->description,
            'settings' => $this->settings,
            'fields' => array_map(
                fn($field) => $field->toArray(),
                $this->fields
            ),
        ];
    }
}
