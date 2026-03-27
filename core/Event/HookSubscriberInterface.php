<?php

declare(strict_types=1);

namespace OOPress\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * HookSubscriberInterface — Extends Symfony's subscriber interface.
 * 
 * This allows modules to subscribe to multiple events from a single class.
 * 
 * @api
 */
interface HookSubscriberInterface extends EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     * 
     * The array keys are event names and the value can be:
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name and the priority
     *  * An array of arrays composed of the method names and priorities
     * 
     * @return array<string, string|array{0: string, 1?: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array;
}
