<?php

declare(strict_types=1);

namespace OOPress\Docs\Event;

use OOPress\Docs\DocGenerator;
use OOPress\Docs\DocResult;
use OOPress\Event\Event;

/**
 * DocGenerateEvent — Dispatched when generating documentation.
 * 
 * Modules can listen to this event to add their own documentation.
 * 
 * @api
 */
class DocGenerateEvent extends Event
{
    public function __construct(
        private readonly DocGenerator $generator,
        private readonly DocResult $result,
    ) {
        parent::__construct();
    }
    
    public function getGenerator(): DocGenerator
    {
        return $this->generator;
    }
    
    public function getResult(): DocResult
    {
        return $this->result;
    }
}