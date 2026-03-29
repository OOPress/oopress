<?php

declare(strict_types=1);

namespace OOPress\UI\Component;

/**
 * ComponentInterface — Contract for all UI components.
 * 
 * @api
 */
interface ComponentInterface
{
    /**
     * Render the component.
     */
    public function render(): string;
    
    /**
     * Get component name.
     */
    public function getName(): string;
    
    /**
     * Get component attributes.
     */
    public function getAttributes(): array;
    
    /**
     * Set component attributes.
     */
    public function setAttributes(array $attributes): self;
}