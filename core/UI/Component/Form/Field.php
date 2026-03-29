<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Form;

use OOPress\UI\Component\ComponentInterface;

/**
 * Field — Form field component.
 * 
 * @api
 */
class Field implements ComponentInterface
{
    private string $name;
    private string $type = 'text';
    private string $label = '';
    private $value = null;
    private string $placeholder = '';
    private bool $required = false;
    private array $options = [];
    private array $attributes = [];
    private ?string $error = null;
    private string $helpText = '';
    
    public function __construct(string $name, string $type = 'text')
    {
        $this->name = $name;
        $this->type = $type;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }
    
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }
    
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }
    
    public function setPlaceholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }
    
    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }
    
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }
    
    public function setError(string $error): self
    {
        $this->error = $error;
        return $this;
    }
    
    public function setHelpText(string $helpText): self
    {
        $this->helpText = $helpText;
        return $this;
    }
    
    public function render(): string
    {
        $html = '<div class="form-group">';
        
        if ($this->label) {
            $html .= sprintf(
                '<label for="%s">%s%s</label>',
                htmlspecialchars($this->name),
                htmlspecialchars($this->label),
                $this->required ? ' <span class="required">*</span>' : ''
            );
        }
        
        $html .= $this->renderField();
        
        if ($this->helpText) {
            $html .= sprintf(
                '<small class="help-text">%s</small>',
                htmlspecialchars($this->helpText)
            );
        }
        
        if ($this->error) {
            $html .= sprintf(
                '<div class="error-message">%s</div>',
                htmlspecialchars($this->error)
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    private function renderField(): string
    {
        $attrs = $this->getFieldAttributes();
        
        switch ($this->type) {
            case 'textarea':
                return sprintf(
                    '<textarea name="%s" %s>%s</textarea>',
                    htmlspecialchars($this->name),
                    $attrs,
                    htmlspecialchars((string) $this->value)
                );
                
            case 'select':
                $html = sprintf('<select name="%s" %s>', htmlspecialchars($this->name), $attrs);
                foreach ($this->options as $value => $label) {
                    $selected = ($this->value == $value) ? 'selected' : '';
                    $html .= sprintf(
                        '<option value="%s" %s>%s</option>',
                        htmlspecialchars($value),
                        $selected,
                        htmlspecialchars($label)
                    );
                }
                $html .= '</select>';
                return $html;
                
            case 'checkbox':
                $checked = $this->value ? 'checked' : '';
                return sprintf(
                    '<input type="checkbox" name="%s" value="1" %s %s>',
                    htmlspecialchars($this->name),
                    $checked,
                    $attrs
                );
                
            case 'radio':
                $html = '';
                foreach ($this->options as $value => $label) {
                    $checked = ($this->value == $value) ? 'checked' : '';
                    $html .= sprintf(
                        '<label><input type="radio" name="%s" value="%s" %s %s> %s</label>',
                        htmlspecialchars($this->name),
                        htmlspecialchars($value),
                        $checked,
                        $attrs,
                        htmlspecialchars($label)
                    );
                }
                return $html;
                
            default:
                return sprintf(
                    '<input type="%s" name="%s" value="%s" placeholder="%s" %s %s>',
                    htmlspecialchars($this->type),
                    htmlspecialchars($this->name),
                    htmlspecialchars((string) $this->value),
                    htmlspecialchars($this->placeholder),
                    $this->required ? 'required' : '',
                    $attrs
                );
        }
    }
    
    private function getFieldAttributes(): string
    {
        $attrs = [];
        
        if ($this->required) {
            $attrs[] = 'required';
        }
        
        foreach ($this->attributes as $key => $value) {
            $attrs[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
        }
        
        return implode(' ', $attrs);
    }
}