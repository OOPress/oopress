<?php

declare(strict_types=1);

namespace OOPress\Log\Handler;

use Psr\Log\LogLevel;

/**
 * FileHandler — Writes logs to files.
 * 
 * GDPR compliant: Logs stored locally with rotation.
 * 
 * @api
 */
class FileHandler implements HandlerInterface
{
    private string $logPath;
    private string $level;
    private array $levels = [];
    private int $maxFiles;
    private int $maxSize;
    private $fileHandle = null;
    
    public function __construct(array $config = [])
    {
        $this->logPath = $config['path'] ?? __DIR__ . '/../../../var/logs/oopress.log';
        $this->level = $config['level'] ?? LogLevel::DEBUG;
        $this->maxFiles = $config['max_files'] ?? 30;
        $this->maxSize = $config['max_size'] ?? 10 * 1024 * 1024; // 10MB
        
        // Ensure directory exists
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $this->initializeLevels();
    }
    
    /**
     * Initialize level priorities.
     */
    private function initializeLevels(): void
    {
        $order = [
            LogLevel::DEBUG => 0,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 3,
            LogLevel::ERROR => 4,
            LogLevel::CRITICAL => 5,
            LogLevel::ALERT => 6,
            LogLevel::EMERGENCY => 7,
        ];
        
        $this->levels = $order;
    }
    
    public function isHandling(string $level): bool
    {
        return $this->levels[$level] >= $this->levels[$this->level];
    }
    
    public function handle(array $record): void
    {
        // Rotate log if needed
        $this->rotateIfNeeded();
        
        // Format log entry
        $entry = $this->format($record);
        
        // Write to file
        $this->openFile();
        fwrite($this->fileHandle, $entry . PHP_EOL);
    }
    
    public function close(): void
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }
    
    /**
     * Format log record as string.
     */
    private function format(array $record): string
    {
        $level = strtoupper($record['level']);
        $datetime = $record['datetime'];
        $channel = $record['channel'];
        $message = $record['message'];
        $context = !empty($record['context']) ? ' ' . json_encode($record['context']) : '';
        
        // Add request context if present
        $request = '';
        if (isset($record['request_uri'])) {
            $request = sprintf(' [%s]', $record['request_uri']);
        }
        
        return sprintf(
            '[%s] %s.%s:%s%s: %s%s',
            $datetime,
            $channel,
            $level,
            $record['pid'],
            $request,
            $message,
            $context
        );
    }
    
    /**
     * Open file handle.
     */
    private function openFile(): void
    {
        if ($this->fileHandle === null) {
            $this->fileHandle = fopen($this->logPath, 'a');
        }
    }
    
    /**
     * Rotate log file if needed.
     */
    private function rotateIfNeeded(): void
    {
        if (!file_exists($this->logPath)) {
            return;
        }
        
        if (filesize($this->logPath) >= $this->maxSize) {
            $this->close();
            $this->rotate();
            $this->openFile();
        }
    }
    
    /**
     * Rotate log files.
     */
    private function rotate(): void
    {
        // Delete oldest file if max files exceeded
        $pattern = $this->logPath . '.*';
        $files = glob($pattern);
        
        if (count($files) >= $this->maxFiles) {
            usort($files, function($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });
            
            $toDelete = array_slice($files, 0, count($files) - $this->maxFiles + 1);
            foreach ($toDelete as $file) {
                unlink($file);
            }
        }
        
        // Rotate current log
        $i = 1;
        while (file_exists($this->logPath . '.' . $i)) {
            $i++;
        }
        
        rename($this->logPath, $this->logPath . '.' . $i);
    }
}