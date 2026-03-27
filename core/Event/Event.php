<?php

declare(strict_types=1);

namespace OOPress\Event;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

/**
 * Base event class for all OOPress events.
 * 
 * This extends Symfony's Event class to maintain compatibility with
 * the EventDispatcher system while providing a namespace for OOPress-specific
 * functionality.
 * 
 * @api
 */
abstract class Event extends SymfonyEvent
{
    /**
     * @var array<string, mixed> Additional event context
     */
    protected array $context = [];

    /**
     * Get event context.
     * 
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set event context.
     * 
     * @param array<string, mixed> $context
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Add a single context value.
     * 
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Get a specific context value.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getContextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
}
