<?php

declare(strict_types=1);

namespace OOPress\Log\Monitor;

use OOPress\Log\Logger;

/**
 * ErrorTracker — Tracks and aggregates errors.
 * 
 * @api
 */
class ErrorTracker
{
    private array $errors = [];
    private array $errorCounts = [];
    
    public function __construct(
        private readonly Logger $logger,
    ) {}
    
    /**
     * Track an error.
     */
    public function track(\Throwable $error, array $context = []): void
    {
        $hash = $this->getErrorHash($error);
        
        $errorData = [
            'hash' => $hash,
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $context,
            'first_occurrence' => time(),
            'last_occurrence' => time(),
            'count' => 1,
        ];
        
        if (isset($this->errors[$hash])) {
            $this->errors[$hash]['count']++;
            $this->errors[$hash]['last_occurrence'] = time();
        } else {
            $this->errors[$hash] = $errorData;
        }
        
        $this->errorCounts[$hash] = ($this->errorCounts[$hash] ?? 0) + 1;
        
        // Log the error
        $this->logger->error($error->getMessage(), [
            'exception' => get_class($error),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'context' => $context,
            'hash' => $hash,
        ]);
    }
    
    /**
     * Get all tracked errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get error counts.
     */
    public function getErrorCounts(): array
    {
        return $this->errorCounts;
    }
    
    /**
     * Get total error count.
     */
    public function getTotalErrorCount(): int
    {
        return array_sum($this->errorCounts);
    }
    
    /**
     * Clear tracked errors.
     */
    public function clear(): void
    {
        $this->errors = [];
        $this->errorCounts = [];
    }
    
    /**
     * Generate a hash for an error.
     */
    private function getErrorHash(\Throwable $error): string
    {
        return md5(
            get_class($error) .
            $error->getFile() .
            $error->getLine() .
            $error->getMessage()
        );
    }
}