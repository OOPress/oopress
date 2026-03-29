<?php

declare(strict_types=1);

namespace OOPress\Log\Event;

use OOPress\Event\Event;

/**
 * LogEvent — Dispatched when a log message is recorded.
 * 
 * Modules can listen to this event to perform actions on log messages.
 * 
 * @api
 */
class LogEvent extends Event
{
    public function __construct(
        private readonly array $record,
    ) {
        parent::__construct();
    }
    
    public function getRecord(): array
    {
        return $this->record;
    }
    
    public function getLevel(): string
    {
        return $this->record['level'];
    }
    
    public function getMessage(): string
    {
        return $this->record['message'];
    }
    
    public function getChannel(): string
    {
        return $this->record['channel'];
    }
}