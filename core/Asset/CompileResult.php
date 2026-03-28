<?php

declare(strict_types=1);

namespace OOPress\Asset;

/**
 * CompileResult — Result of asset compilation.
 * 
 * @api
 */
class CompileResult
{
    private bool $success = false;
    private bool $skipped = false;
    private string $outputPath = '';
    private int $outputSize = 0;
    private array $files = [];
    private ?string $error = null;
    private ?string $skipReason = null;
    
    public function __construct(
        public readonly string $type,
    ) {}
    
    public function setSuccess(string $outputPath, int $outputSize): void
    {
        $this->success = true;
        $this->outputPath = $outputPath;
        $this->outputSize = $outputSize;
    }
    
    public function setFailure(string $error): void
    {
        $this->success = false;
        $this->error = $error;
    }
    
    public function setSkipped(string $reason): void
    {
        $this->skipped = true;
        $this->skipReason = $reason;
    }
    
    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }
    
    public function isSuccess(): bool
    {
        return $this->success;
    }
    
    public function isSkipped(): bool
    {
        return $this->skipped;
    }
    
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }
    
    public function getOutputSize(): int
    {
        return $this->outputSize;
    }
    
    public function getFileCount(): int
    {
        return count($this->files);
    }
    
    public function getError(): ?string
    {
        return $this->error;
    }
    
    public function getSkipReason(): ?string
    {
        return $this->skipReason;
    }
    
    public function getSummary(): string
    {
        if ($this->skipped) {
            return sprintf("Skipped: %s", $this->skipReason);
        }
        
        if (!$this->success) {
            return sprintf("Failed: %s", $this->error);
        }
        
        return sprintf(
            "Compiled %d file(s) -> %s (%s)",
            $this->getFileCount(),
            basename($this->outputPath),
            $this->formatSize($this->outputSize)
        );
    }
    
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}