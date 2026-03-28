<?php

declare(strict_types=1);

namespace OOPress\Content\Field\Type;

use OOPress\Content\Field\FieldDefinition;
use OOPress\Content\Field\FieldTypeInterface;
use OOPress\Content\ContentTranslation;

/**
 * NumberField — Numeric field (integer, float, decimal).
 * 
 * @api
 */
class NumberField implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'number';
    }
    
    public function getLabel(): string
    {
        return 'Number';
    }
    
    public function validate(mixed $value, FieldDefinition $definition): array
    {
        $errors = [];
        
        if ($definition->required && ($value === null || $value === '')) {
            $errors[] = sprintf('%s is required', $definition->label);
            return $errors;
        }
        
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $errors[] = sprintf('%s must be a number', $definition->label);
            return $errors;
        }
        
        $numericValue = (float) $value;
        
        $min = $definition->settings['min'] ?? null;
        if ($min !== null && $numericValue < $min) {
            $errors[] = sprintf('%s must be at least %s', $definition->label, $min);
        }
        
        $max = $definition->settings['max'] ?? null;
        if ($max !== null && $numericValue > $max) {
            $errors[] = sprintf('%s cannot exceed %s', $definition->label, $max);
        }
        
        $step = $definition->settings['step'] ?? null;
        if ($step !== null && $step > 0) {
            $precision = $definition->settings['decimal_places'] ?? 0;
            $rounded = round($numericValue / $step) * $step;
            if (abs($numericValue - $rounded) > 0.000001) {
                $errors[] = sprintf('%s must be a multiple of %s', $definition->label, $step);
            }
        }
        
        return $errors;
    }
    
    public function sanitize(mixed $value, FieldDefinition $definition): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $numericValue = (float) $value;
        $decimalPlaces = $definition->settings['decimal_places'] ?? 0;
        
        if ($decimalPlaces > 0) {
            return round($numericValue, $decimalPlaces);
        }
        
        return (int) $numericValue;
    }
    
    public function format(mixed $value, FieldDefinition $definition, ?ContentTranslation $translation = null): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        
        $decimalPlaces = $definition->settings['decimal_places'] ?? 0;
        $thousandsSeparator = $definition->settings['thousands_separator'] ?? ',';
        $decimalSeparator = $definition->settings['decimal_separator'] ?? '.';
        
        $number = (float) $value;
        $formatted = number_format($number, $decimalPlaces, $decimalSeparator, $thousandsSeparator);
        
        $prefix = $definition->settings['prefix'] ?? '';
        $suffix = $definition->settings['suffix'] ?? '';
        
        return $prefix . $formatted . $suffix;
    }
    
    public function getDefaultValue(FieldDefinition $definition): mixed
    {
        return $definition->settings['default'] ?? null;
    }
    
    public function getConfigSchema(): array
    {
        return [
            'min' => [
                'type' => 'number',
                'label' => 'Minimum value',
                'required' => false,
            ],
            'max' => [
                'type' => 'number',
                'label' => 'Maximum value',
                'required' => false,
            ],
            'step' => [
                'type' => 'number',
                'label' => 'Step',
                'description' => 'Increment step',
                'required' => false,
            ],
            'decimal_places' => [
                'type' => 'number',
                'label' => 'Decimal places',
                'description' => 'Number of decimal places to display',
                'default' => 0,
            ],
            'decimal_separator' => [
                'type' => 'text',
                'label' => 'Decimal separator',
                'default' => '.',
            ],
            'thousands_separator' => [
                'type' => 'text',
                'label' => 'Thousands separator',
                'default' => ',',
            ],
            'prefix' => [
                'type' => 'text',
                'label' => 'Prefix',
                'description' => 'Text to display before the number',
            ],
            'suffix' => [
                'type' => 'text',
                'label' => 'Suffix',
                'description' => 'Text to display after the number',
            ],
            'default' => [
                'type' => 'number',
                'label' => 'Default value',
                'required' => false,
            ],
        ];
    }
    
    public function renderWidget(string $name, mixed $value, FieldDefinition $definition, array $attributes = []): string
    {
        $value = $value !== null ? (float) $value : '';
        $min = $definition->settings['min'] ?? '';
        $max = $definition->settings['max'] ?? '';
        $step = $definition->settings['step'] ?? ($definition->settings['decimal_places'] > 0 ? '0.01' : '1');
        $placeholder = $definition->settings['placeholder'] ?? '';
        $required = $definition->required ? 'required' : '';
        
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= sprintf(' %s="%s"', $key, htmlspecialchars($val, ENT_QUOTES));
        }
        
        return sprintf(
            '<input type="number" name="%s" value="%s" min="%s" max="%s" step="%s" placeholder="%s" %s %s>',
            htmlspecialchars($name, ENT_QUOTES),
            $value,
            $min,
            $max,
            $step,
            htmlspecialchars($placeholder, ENT_QUOTES),
            $required,
            $attrs
        );
    }
}
