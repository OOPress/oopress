<?php

declare(strict_types=1);

namespace OOPress\Content\Field\Type;

use OOPress\Content\Field\FieldDefinition;
use OOPress\Content\Field\FieldTypeInterface;
use OOPress\Content\ContentTranslation;

/**
 * BooleanField — Checkbox field.
 * 
 * @api
 */
class BooleanField implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'boolean';
    }
    
    public function getLabel(): string
    {
        return 'Boolean';
    }
    
    public function validate(mixed $value, FieldDefinition $definition): array
    {
        $errors = [];
        
        if ($definition->required && $value === null) {
            $errors[] = sprintf('%s is required', $definition->label);
        }
        
        if ($value !== null && !is_bool($value) && $value !== 0 && $value !== 1 && $value !== '0' && $value !== '1') {
            $errors[] = sprintf('%s must be true or false', $definition->label);
        }
        
        return $errors;
    }
    
    public function sanitize(mixed $value, FieldDefinition $definition): mixed
    {
        if ($value === null || $value === '') {
            return false;
        }
        
        return (bool) $value;
    }
    
    public function format(mixed $value, FieldDefinition $definition, ?ContentTranslation $translation = null): string
    {
        $onText = $definition->settings['on_text'] ?? 'Yes';
        $offText = $definition->settings['off_text'] ?? 'No';
        
        return $value ? $onText : $offText;
    }
    
    public function getDefaultValue(FieldDefinition $definition): mixed
    {
        return $definition->settings['default'] ?? false;
    }
    
    public function getConfigSchema(): array
    {
        return [
            'on_text' => [
                'type' => 'text',
                'label' => 'Text for "On" value',
                'default' => 'Yes',
            ],
            'off_text' => [
                'type' => 'text',
                'label' => 'Text for "Off" value',
                'default' => 'No',
            ],
            'default' => [
                'type' => 'boolean',
                'label' => 'Default value',
                'default' => false,
            ],
        ];
    }
    
    public function renderWidget(string $name, mixed $value, FieldDefinition $definition, array $attributes = []): string
    {
        $checked = $value ? 'checked' : '';
        
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= sprintf(' %s="%s"', $key, htmlspecialchars($val, ENT_QUOTES));
        }
        
        return sprintf(
            '<input type="checkbox" name="%s" value="1" %s %s>',
            htmlspecialchars($name, ENT_QUOTES),
            $checked,
            $attrs
        );
    }
}
