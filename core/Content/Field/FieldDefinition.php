<?php

declare(strict_types=1);

namespace OOPress\Content\Field;

/**
 * FieldDefinition — Defines a field for a content type.
 * 
 * @api
 */
class FieldDefinition
{
    /**
     * @param string $name Machine name (e.g., "subtitle")
     * @param string $type Field type (e.g., "text", "number", "image")
     * @param string $label Human-readable label
     * @param array<string, mixed> $settings Field-specific settings
     * @param bool $required Whether the field is required
     * @param bool $translatable Whether the field can be translated
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $label,
        public readonly array $settings = [],
        public readonly bool $required = false,
        public readonly bool $translatable = true,
        public readonly int $weight = 0,
    ) {}
    
    /**
     * Create from array.
     */
    public static function fromArray(string $name, array $data): self
    {
        return new self(
            name: $name,
            type: $data['type'] ?? 'text',
            label: $data['label'] ?? $name,
            settings: $data['settings'] ?? [],
            required: $data['required'] ?? false,
            translatable: $data['translatable'] ?? true,
            weight: $data['weight'] ?? 0,
        );
    }
    
    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'settings' => $this->settings,
            'required' => $this->required,
            'translatable' => $this->translatable,
            'weight' => $this->weight,
        ];
    }
    
    /**
     * Get the field's storage column name in the translation table.
     */
    public function getColumnName(): string
    {
        return 'field_' . $this->name;
    }
}
