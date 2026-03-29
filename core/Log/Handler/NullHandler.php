<?php

declare(strict_types=1);

namespace OOPress\Log\Handler;

/**
 * NullHandler — Discards all logs (useful for testing).
 * 
 * @internal
 */
class NullHandler implements HandlerInterface
{
    public function isHandling(string $level): bool
    {
        return true;
    }
    
    public function handle(array $record): void
    {
        // Do nothing
    }
    
    public function close(): void
    {
        // Do nothing
    }
}