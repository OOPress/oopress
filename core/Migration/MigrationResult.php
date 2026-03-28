<?php

declare(strict_types=1);

namespace OOPress\Migration;

/**
 * MigrationResult — Value object for migration execution results.
 * 
 * @api
 */
class MigrationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $output,
        public readonly int $migrationsExecuted = 0,
        public readonly bool $dryRun = false,
        public readonly ?string $error = null,
    ) {}
    
    /**
     * Check if any migrations were executed.
     */
    public function hasMigrations(): bool
    {
        return $this->migrationsExecuted > 0;
    }
    
    /**
     * Get the output as an array of lines.
     * 
     * @return array<string>
     */
    public function getOutputLines(): array
    {
        return explode("\n", trim($this->output));
    }
    
    /**
     * Get the error message if present.
     */
    public function getErrorMessage(): ?string
    {
        if ($this->success) {
            return null;
        }
        
        return $this->error ?? 'Unknown error occurred during migration';
    }
    
    /**
     * Create a success result.
     */
    public static function success(string $output, int $executed = 0): self
    {
        return new self(
            success: true,
            output: $output,
            migrationsExecuted: $executed,
        );
    }
    
    /**
     * Create a failure result.
     */
    public static function failure(string $output, string $error): self
    {
        return new self(
            success: false,
            output: $output,
            error: $error,
        );
    }
}
