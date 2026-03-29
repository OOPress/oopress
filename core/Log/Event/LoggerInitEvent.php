<?php

declare(strict_types=1);

namespace OOPress\Log\Event;

use OOPress\Log\Logger;
use OOPress\Event\Event;

/**
 * LoggerInitEvent — Dispatched when logger is initialized.
 * 
 * Modules can listen to this event to add custom log processors or handlers.
 * 
 * @api
 */
class LoggerInitEvent extends Event
{
    public function __construct(
        private readonly Logger $logger,
    ) {
        parent::__construct();
    }
    
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}