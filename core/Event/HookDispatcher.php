<?php

declare(strict_types=1);

namespace OOPress\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

/**
 * HookDispatcher — Wrapper around Symfony's EventDispatcher that adds filter hook support.
 * 
 * This provides two methods:
 * - dispatch(): Standard Symfony-style action hooks (fire and forget)
 * - filter(): OOPress-style filter hooks (value modification)
 * 
 * Module authors should use this class exclusively for both action and filter hooks.
 * 
 * @api
 */
class HookDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly SymfonyEventDispatcherInterface $dispatcher,
    ) {}

    /**
     * Dispatch an action event.
     * 
     * Action events are fire-and-forget. Multiple listeners can respond,
     * but they don't return values to the caller.
     * 
     * @param Event $event The event to dispatch
     * @param string|null $eventName Optional event name (uses event class name if null)
     * @return Event The dispatched event
     */
    public function dispatch(Event $event, ?string $eventName = null): Event
    {
        return $this->dispatcher->dispatch($event, $eventName);
    }

    /**
     * Dispatch a filter event.
     * 
     * Filter events pass a value through a chain of listeners. Each listener
     * can modify the value before passing it to the next listener.
     * 
     * @param FilterEvent $event The filter event to dispatch
     * @param string|null $eventName Optional event name (uses event class name if null)
     * @return mixed The final value after all listeners have processed it
     */
    public function filter(FilterEvent $event, ?string $eventName = null): mixed
    {
        $this->dispatcher->dispatch($event, $eventName);
        return $event->getValue();
    }

    /**
     * Convenience method for simple filter hooks.
     * 
     * This allows module authors to write:
     * 
     * $title = $hookDispatcher->applyFilters('page.title', $title, $context);
     * 
     * Instead of:
     * 
     * $event = new FilterEvent($title);
     * $event->setContext($context);
     * $title = $hookDispatcher->filter($event, 'page.title');
     * 
     * @param string $hookName The hook name
     * @param mixed $value The value to filter
     * @param array<string, mixed> $context Additional context
     * @return mixed The filtered value
     */
    public function applyFilters(string $hookName, mixed $value, array $context = []): mixed
    {
        $event = new FilterEvent($value);
        $event->setContext($context);
        return $this->filter($event, $hookName);
    }

    /**
     * Convenience method for simple action hooks.
     * 
     * @param string $hookName The hook name
     * @param array<string, mixed> $context Additional context
     */
    public function doAction(string $hookName, array $context = []): void
    {
        $event = new class($context) extends Event {
            public function __construct(array $context)
            {
                parent::__construct();
                $this->context = $context;
            }
        };
        
        $this->dispatch($event, $hookName);
    }

    /**
     * Add a listener to an event.
     * 
     * @param string $eventName The event name to listen for
     * @param callable $listener The listener to call
     * @param int $priority Higher priority = executed earlier
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Remove a listener.
     * 
     * @param string $eventName The event name
     * @param callable $listener The listener to remove
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * Check if listeners exist for an event.
     * 
     * @param string $eventName The event name
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return $this->dispatcher->hasListeners($eventName);
    }

    /**
     * Get all listeners for an event.
     * 
     * @param string $eventName The event name
     * @return iterable<callable>
     */
    public function getListeners(string $eventName): iterable
    {
        return $this->dispatcher->getListeners($eventName);
    }
}
