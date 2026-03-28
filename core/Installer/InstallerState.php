<?php

declare(strict_types=1);

namespace OOPress\Installer;

/**
 * InstallerState — Tracks installation progress.
 * 
 * @api
 */
class InstallerState
{
    private \DateTimeImmutable $startTime;
    private \DateTimeImmutable $completeTime;
    private bool $completed = false;
    
    /** @var array<string, array{success: bool, error?: string, data?: mixed}> */
    private array $steps = [];
    
    /** @var array<string> */
    private array $errors = [];
    
    /** @var array<string> */
    private array $warnings = [];
    
    private ?int $adminUserId = null;
    
    public function __construct()
    {
        $this->startTime = new \DateTimeImmutable();
    }
    
    public function start(): void
    {
        $this->startTime = new \DateTimeImmutable();
    }
    
    public function complete(): void
    {
        $this->completed = true;
        $this->completeTime = new \DateTimeImmutable();
    }
    
    public function addStep(string $step, bool $success, ?string $error = null, mixed $data = null): void
    {
        $this->steps[$step] = [
            'success' => $success,
            'error' => $error,
            'data' => $data,
        ];
    }
    
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }
    
    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }
    
    public function setAdminUserId(int $userId): void
    {
        $this->adminUserId = $userId;
    }
    
    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }
    
    public function getCompleteTime(): ?\DateTimeImmutable
    {
        return $this->completeTime ?? null;
    }
    
    public function isCompleted(): bool
    {
        return $this->completed;
    }
    
    public function getSteps(): array
    {
        return $this->steps;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getWarnings(): array
    {
        return $this->warnings;
    }
    
    public function getAdminUserId(): ?int
    {
        return $this->adminUserId;
    }
    
    public function getDuration(): ?float
    {
        $end = $this->completeTime ?? new \DateTimeImmutable();
        return $end->getTimestamp() - $this->startTime->getTimestamp();
    }
    
    public function toArray(): array
    {
        return [
            'start_time' => $this->startTime->format(\DateTimeInterface::ISO8601),
            'complete_time' => $this->completeTime?->format(\DateTimeInterface::ISO8601),
            'completed' => $this->completed,
            'duration' => $this->getDuration(),
            'steps' => $this->steps,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'admin_user_id' => $this->adminUserId,
        ];
    }
}
