<?php

declare(strict_types=1);

namespace OOPress\Content\Field\Type;

use OOPress\Content\Field\FieldDefinition;
use OOPress\Content\Field\FieldTypeInterface;
use OOPress\Content\ContentTranslation;

/**
 * TextareaField — Multi-line text field.
 * 
 * @api
 */
class TextareaField implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'textarea';
    }
    
    public function getLabel(): string
    {
        return 'Textarea';
    }
    
    public function validate(mixed $value, FieldDefinition $definition): array
    {
        $errors = [];
        
        if ($definition->required && empty($value)) {
            $errors[] = sprintf('%s is required', $definition->label);
            return $errors;
        }
        
        if (!empty($value) && !is_string($value)) {
            $errors[] = sprintf('%s must be text', $definition->label);
        }
        
        $maxLength = $definition->settings['max_length'] ?? null;
        if ($maxLength && strlen((string) $value) > $maxLength) {
            $errors[] = sprintf('%s cannot exceed %d characters', $definition->label, $maxLength);
        }
        
        $minLength = $definition->settings['min_length'] ?? null;
        if ($minLength && strlen((string) $value) < $minLength) {
            $errors[] = sprintf('%s must be at least %d characters', $definition->label, $minLength);
        }
        
        return $errors;
    }
    
    public function sanitize(mixed $value, FieldDefinition $definition): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $value = (string) $value;
        
        $maxLength = $definition->settings['max_length'] ?? null;
        if ($maxLength && strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }
        
        return $value;
    }
    
    public function format(mixed $value, FieldDefinition $definition, ?ContentTranslation $translation = null): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        
        $format = $definition->settings['format'] ?? 'plain';
        
        if ($format === 'markdown') {
            // Placeholder for Markdown rendering
            return nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }
        
        if ($format === 'html') {
            return (string) $value;
        }
        
        return nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
    }
    
    public function getDefaultValue(FieldDefinition $definition): mixed
    {
        return $definition->settings['default'] ?? null;
    }
    
    public function getConfigSchema(): array
    {
        return [
            'max_length' => [
                'type' => 'number',
                'label' => 'Maximum length',
                'required' => false,
            ],
            'min_length' => [
                'type' => 'number',
                'label' => 'Minimum length',
                'required' => false,
            ],
            'rows' => [
                'type' => 'number',
                'label' => 'Rows',
                'description' => 'Number of rows in textarea',
                'default' => 5,
            ],
            'format' => [
                'type' => 'select',
                'label' => 'Text format',
                'options' => [
                    'plain' => 'Plain text',
                    'markdown' => 'Markdown',
                    'html' => 'HTML',
                ],
                'default' => 'plain',
            ],
            'default' => [
                'type' => 'textarea',
                'label' => 'Default value',
                'required' => false,
            ],
        ];
    }
    
    public function renderWidget(string $name, mixed $value, FieldDefinition $definition, array $attributes = []): string
    {
        $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $rows = $definition->settings['rows'] ?? 5;
        $placeholder = $definition->settings['placeholder'] ?? '';
        $required = $definition->required ? 'required' : '';
        
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= sprintf(' %s="%s"', $key, htmlspecialchars($val, ENT_QUOTES));
        }
        
        return sprintf(
            '<textarea name="%s" rows="%d" placeholder="%s" %s %s>%s</textarea>',
            htmlspecialchars($name, ENT_QUOTES),
            $rows,
            htmlspecialchars($placeholder, ENT_QUOTES),
            $required,
            $attrs,
            $value
        );
    }
}
