<?php

declare(strict_types=1);

namespace OOPress\Event;

/**
 * FilterEvent — An event that can modify and return a value.
 * 
 * This is the foundation of OOPress's filter hook system. Unlike standard
 * Symfony events which are fire-and-forget, FilterEvents allow listeners
 * to modify a value as it passes through the event chain.
 * 
 * @api
 */
class FilterEvent extends Event
{
    public function __construct(
        private mixed $value,
    ) {
        parent::__construct();
    }

    /**
     * Get the current value.
     * 
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Set a new value.
     * 
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Check if the value has been modified.
     * 
     * This is useful for debugging and performance monitoring.
     * 
     * @return bool
     */
    public function isModified(): bool
    {
        // This is a simplistic implementation. In a more advanced version,
        // we might track original value and compare.
        return true; // We don't track original yet
    }
}
