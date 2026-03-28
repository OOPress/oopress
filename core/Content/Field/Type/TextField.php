<?php

declare(strict_types=1);

namespace OOPress\Content\Field\Type;

use OOPress\Content\Field\FieldDefinition;
use OOPress\Content\Field\FieldTypeInterface;
use OOPress\Content\ContentTranslation;

/**
 * TextField — Single-line text field.
 * 
 * @api
 */
class TextField implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'text';
    }
    
    public function getLabel(): string
    {
        return 'Text';
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
        
        $pattern = $definition->settings['pattern'] ?? null;
        if ($pattern && !empty($value) && !preg_match($pattern, (string) $value)) {
            $errors[] = sprintf('%s does not match required pattern', $definition->label);
        }
        
        return $errors;
    }
    
    public function sanitize(mixed $value, FieldDefinition $definition): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $value = trim((string) $value);
        
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
        
        $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        
        if ($definition->settings['linkify'] ?? false) {
            $value = $this->linkify($value);
        }
        
        return $value;
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
                'description' => 'Maximum number of characters allowed',
                'required' => false,
            ],
            'min_length' => [
                'type' => 'number',
                'label' => 'Minimum length',
                'description' => 'Minimum number of characters required',
                'required' => false,
            ],
            'pattern' => [
                'type' => 'text',
                'label' => 'Pattern',
                'description' => 'Regular expression pattern to validate against',
                'required' => false,
            ],
            'default' => [
                'type' => 'text',
                'label' => 'Default value',
                'description' => 'Default value for new content',
                'required' => false,
            ],
            'placeholder' => [
                'type' => 'text',
                'label' => 'Placeholder',
                'description' => 'Placeholder text for the input',
                'required' => false,
            ],
            'linkify' => [
                'type' => 'boolean',
                'label' => 'Linkify URLs',
                'description' => 'Automatically convert URLs to links',
                'default' => false,
            ],
        ];
    }
    
    public function renderWidget(string $name, mixed $value, FieldDefinition $definition, array $attributes = []): string
    {
        $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $placeholder = $definition->settings['placeholder'] ?? '';
        $maxLength = $definition->settings['max_length'] ?? '';
        $required = $definition->required ? 'required' : '';
        
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= sprintf(' %s="%s"', $key, htmlspecialchars($val, ENT_QUOTES));
        }
        
        return sprintf(
            '<input type="text" name="%s" value="%s" placeholder="%s" maxlength="%s" %s %s>',
            htmlspecialchars($name, ENT_QUOTES),
            $value,
            htmlspecialchars($placeholder, ENT_QUOTES),
            $maxLength,
            $required,
            $attrs
        );
    }
    
    private function linkify(string $text): string
    {
        $pattern = '/(https?:\/\/[^\s]+)/';
        return preg_replace($pattern, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $text);
    }
}
