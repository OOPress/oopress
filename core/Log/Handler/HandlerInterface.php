<?php

declare(strict_types=1);

namespace OOPress\Log\Handler;

/**
 * HandlerInterface — Contract for log handlers.
 * 
 * @api
 */
interface HandlerInterface
{
    /**
     * Check if handler handles this level.
     */
    public function isHandling(string $level): bool;
    
    /**
     * Handle a log record.
     */
    public function handle(array $record): void;
    
    /**
     * Close the handler.
     */
    public function close(): void;
}