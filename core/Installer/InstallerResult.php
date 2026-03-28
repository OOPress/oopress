<?php

declare(strict_types=1);

namespace OOPress\Installer;

/**
 * InstallerResult — Result of an installation attempt.
 * 
 * @api
 */
class InstallerResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?InstallerState $state = null,
        public readonly ?int $adminUserId = null,
        public readonly array $errors = [],
        public readonly array $warnings = [],
    ) {}
    
    public static function success(string $message, InstallerState $state, ?int $userId = null): self
    {
        return new self(
            success: true,
            message: $message,
            state: $state,
            adminUserId: $userId,
            errors: $state->getErrors(),
            warnings: $state->getWarnings(),
        );
    }
    
    public static function failure(string $message, ?InstallerState $state = null, array $errors = []): self
    {
        return new self(
            success: false,
            message: $message,
            state: $state,
            errors: $errors,
            warnings: $state?->getWarnings() ?? [],
        );
    }
    
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
    
    public function getSummary(): string
    {
        $summary = $this->message . "\n";
        
        if ($this->state) {
            $summary .= sprintf(
                "Duration: %.2f seconds\n",
                $this->state->getDuration()
            );
            
            $completedSteps = count(array_filter($this->state->getSteps(), fn($step) => $step['success']));
            $totalSteps = count($this->state->getSteps());
            $summary .= sprintf(
                "Steps completed: %d/%d\n",
                $completedSteps,
                $totalSteps
            );
        }
        
        if ($this->errors) {
            $summary .= "\nErrors:\n  - " . implode("\n  - ", $this->errors) . "\n";
        }
        
        if ($this->warnings) {
            $summary .= "\nWarnings:\n  - " . implode("\n  - ", $this->warnings) . "\n";
        }
        
        return $summary;
    }
}
