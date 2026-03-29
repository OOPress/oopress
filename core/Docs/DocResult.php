<?php

declare(strict_types=1);

namespace OOPress\Docs;

/**
 * DocResult — Result of documentation generation.
 * 
 * @api
 */
class DocResult
{
    private array $generated = [];
    private array $errors = [];
    private \DateTimeImmutable $startTime;
    private \DateTimeImmutable $endTime;
    
    public function __construct()
    {
        $this->startTime = new \DateTimeImmutable();
    }
    
    public function addGenerated(string $file): void
    {
        $this->generated[] = $file;
    }
    
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }
    
    public function complete(): void
    {
        $this->endTime = new \DateTimeImmutable();
    }
    
    public function isSuccess(): bool
    {
        return empty($this->errors);
    }
    
    public function getGenerated(): array
    {
        return $this->generated;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getGeneratedCount(): int
    {
        return count($this->generated);
    }
    
    public function getErrorCount(): int
    {
        return count($this->errors);
    }
    
    public function getDuration(): float
    {
        if (!isset($this->endTime)) {
            return 0;
        }
        return $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
    }
    
    public function getSummary(): string
    {
        $summary = sprintf("Generated %d files in %.2f seconds\n", $this->getGeneratedCount(), $this->getDuration());
        
        if ($this->getErrorCount() > 0) {
            $summary .= sprintf("Errors: %d\n", $this->getErrorCount());
            foreach ($this->errors as $error) {
                $summary .= "  - $error\n";
            }
        }
        
        return $summary;
    }
}