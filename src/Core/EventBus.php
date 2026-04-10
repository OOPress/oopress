<?php

declare(strict_types=1);

namespace OOPress\Core;

class EventBus
{
    private array $listeners = [];
    private array $sorted = [];
    
    /**
     * Register a listener for an event
     */
    public function listen(string $event, callable|string $listener, int $priority = 0): void
    {
        $this->listeners[$event][$priority][] = $listener;
        unset($this->sorted[$event]);
    }
    
    /**
     * Dispatch an event to all registered listeners
     */
    public function dispatch(object $event): void
    {
        $eventName = get_class($event);
        
        if (!isset($this->listeners[$eventName])) {
            return;
        }
        
        $listeners = $this->getSortedListeners($eventName);
        
        foreach ($listeners as $listener) {
            if (is_callable($listener)) {
                $listener($event);
            } elseif (is_string($listener) && class_exists($listener)) {
                $handler = new $listener();
                if (method_exists($handler, 'handle')) {
                    $handler->handle($event);
                }
            }
        }
    }
    
    /**
     * Get listeners sorted by priority
     */
    private function getSortedListeners(string $eventName): array
    {
        if (!isset($this->sorted[$eventName])) {
            $listeners = $this->listeners[$eventName];
            krsort($listeners);
            $this->sorted[$eventName] = array_merge(...$listeners);
        }
        
        return $this->sorted[$eventName];
    }
    
    /**
     * Remove all listeners for an event
     */
    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
        unset($this->sorted[$event]);
    }
}