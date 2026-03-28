<?php

declare(strict_types=1);

namespace OOPress\Console\Event;

use OOPress\Console\ConsoleApplication;
use OOPress\Event\Event;

/**
 * ConsoleCommandsEvent — Dispatched when registering console commands.
 * 
 * Modules can listen to this event to add their own CLI commands.
 * 
 * @api
 */
class ConsoleCommandsEvent extends Event
{
    public function __construct(
        private readonly ConsoleApplication $application,
    ) {
        parent::__construct();
    }
    
    public function getApplication(): ConsoleApplication
    {
        return $this->application;
    }
}