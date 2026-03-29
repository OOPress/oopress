<?php

declare(strict_types=1);

namespace OOPress\UI\Component\Form;

use OOPress\UI\Component\ComponentInterface;

/**
 * Form — Form component.
 * 
 * @api
 */
class Form implements ComponentInterface
{
    private string $name;
    private string $method = 'POST';
    private string $action = '';
    private array $fields = [];
    private array $attributes = [];
    private array $errors = [];
    private array $values = [];
    private ?string $csrfToken = null;
    
    public function __construct(string $name, string $action = '', string $method = 'POST')
    {
        $this->name = $name;
        $this->action = $action;
        $this->method = strtoupper($method);
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
    
    /**
     * Add a field to the form.
     */
    public function addField(Field $field): self
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }
    
    /**
     * Get a field by name.
     */
    public function getField(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
    }
    
    /**
     * Set form values.
     */
    public function setValues(array $values): self
    {
        $this->values = $values;
        
        foreach ($this->fields as $field) {
            if (isset($values[$field->getName()])) {
                $field->setValue($values[$field->getName()]);
            }
        }
        
        return $this;
    }
    
    /**
     * Set form errors.
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        
        foreach ($this->fields as $field) {
            if (isset($errors[$field->getName()])) {
                $field->setError($errors[$field->getName()]);
            }
        }
        
        return $this;
    }
    
    /**
     * Set CSRF token.
     */
    public function setCsrfToken(string $token): self
    {
        $this->csrfToken = $token;
        return $this;
    }
    
    /**
     * Check if form is valid.
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }
    
    /**
     * Render the form.
     */
    public function render(): string
    {
        $html = sprintf(
            '<form name="%s" method="%s" action="%s" %s>',
            htmlspecialchars($this->name),
            htmlspecialchars($this->method),
            htmlspecialchars($this->action),
            $this->renderAttributes()
        );
        
        // Add CSRF token
        if ($this->csrfToken) {
            $html .= sprintf(
                '<input type="hidden" name="_csrf_token" value="%s">',
                htmlspecialchars($this->csrfToken)
            );
        }
        
        // Render fields
        foreach ($this->fields as $field) {
            $html .= $field->render();
        }
        
        $html .= '</form>';
        
        return $html;
    }
    
    private function renderAttributes(): string
    {
        $attrs = [];
        foreach ($this->attributes as $key => $value) {
            $attrs[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
        }
        return implode(' ', $attrs);
    }
}