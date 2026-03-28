<?php

declare(strict_types=1);

namespace OOPress\Update;

/**
 * UpdateResult — Result of an update operation.
 * 
 * @api
 */
class UpdateResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly int $migrationsExecuted = 0,
        public readonly array $errors = [],
        public readonly float $duration = 0,
    ) {}
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    public function getErrorMessage(): string
    {
        if (!$this->success) {
            return $this->message;
        }
        
        return implode("\n", $this->errors);
    }
    
    public function getSummary(): string
    {
        $summary = $this->message . "\n";
        $summary .= sprintf("Duration: %.2f seconds\n", $this->duration);
        
        if ($this->migrationsExecuted > 0) {
            $summary .= sprintf("Migrations executed: %d\n", $this->migrationsExecuted);
        }
        
        if ($this->errors) {
            $summary .= "\nErrors:\n  - " . implode("\n  - ", $this->errors) . "\n";
        }
        
        return $summary;
    }
}