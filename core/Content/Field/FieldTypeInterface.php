<?php

declare(strict_types=1);

namespace OOPress\Content\Field;

use OOPress\Content\ContentTranslation;

/**
 * FieldTypeInterface — Contract for all field types.
 * 
 * @api
 */
interface FieldTypeInterface
{
    /**
     * Get the field type identifier (e.g., "text", "number", "image").
     */
    public function getType(): string;
    
    /**
     * Get the human-readable label.
     */
    public function getLabel(): string;
    
    /**
     * Validate field value.
     * 
     * @param mixed $value The value to validate
     * @param FieldDefinition $definition The field definition
     * @return array<string> List of validation errors
     */
    public function validate(mixed $value, FieldDefinition $definition): array;
    
    /**
     * Sanitize/format field value for storage.
     * 
     * @param mixed $value The raw value
     * @param FieldDefinition $definition The field definition
     * @return mixed The sanitized value
     */
    public function sanitize(mixed $value, FieldDefinition $definition): mixed;
    
    /**
     * Format field value for display.
     * 
     * @param mixed $value The stored value
     * @param FieldDefinition $definition The field definition
     * @param ContentTranslation|null $translation The content translation context
     * @return string The formatted value
     */
    public function format(mixed $value, FieldDefinition $definition, ?ContentTranslation $translation = null): string;
    
    /**
     * Get the default value for this field.
     * 
     * @param FieldDefinition $definition The field definition
     * @return mixed
     */
    public function getDefaultValue(FieldDefinition $definition): mixed;
    
    /**
     * Get the field's configuration form schema.
     * 
     * @return array<string, mixed>
     */
    public function getConfigSchema(): array;
    
    /**
     * Render the field's input widget.
     * 
     * @param string $name The field name
     * @param mixed $value The current value
     * @param FieldDefinition $definition The field definition
     * @param array<string, mixed> $attributes Additional HTML attributes
     * @return string The HTML widget
     */
    public function renderWidget(string $name, mixed $value, FieldDefinition $definition, array $attributes = []): string;
}
