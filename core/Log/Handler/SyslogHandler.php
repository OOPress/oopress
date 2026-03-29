<?php

declare(strict_types=1);

namespace OOPress\Log\Handler;

use Psr\Log\LogLevel;

/**
 * SyslogHandler — Writes logs to syslog.
 * 
 * @api
 */
class SyslogHandler implements HandlerInterface
{
    private string $ident;
    private int $facility;
    private string $level;
    private array $levels = [];
    private bool $opened = false;
    
    public function __construct(array $config = [])
    {
        $this->ident = $config['ident'] ?? 'oopress';
        $this->facility = $config['facility'] ?? LOG_USER;
        $this->level = $config['level'] ?? LogLevel::DEBUG;
        
        $this->initializeLevels();
        $this->open();
    }
    
    /**
     * Initialize level priorities and syslog mapping.
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
    
    /**
     * Map PSR-3 level to syslog priority.
     */
    private function toSyslogPriority(string $level): int
    {
        return match($level) {
            LogLevel::DEBUG => LOG_DEBUG,
            LogLevel::INFO => LOG_INFO,
            LogLevel::NOTICE => LOG_NOTICE,
            LogLevel::WARNING => LOG_WARNING,
            LogLevel::ERROR => LOG_ERR,
            LogLevel::CRITICAL => LOG_CRIT,
            LogLevel::ALERT => LOG_ALERT,
            LogLevel::EMERGENCY => LOG_EMERG,
            default => LOG_INFO,
        };
    }
    
    private function open(): void
    {
        if (!$this->opened) {
            openlog($this->ident, LOG_ODELAY, $this->facility);
            $this->opened = true;
        }
    }
    
    public function isHandling(string $level): bool
    {
        return $this->levels[$level] >= $this->levels[$this->level];
    }
    
    public function handle(array $record): void
    {
        $this->open();
        
        $priority = $this->toSyslogPriority($record['level']);
        $message = sprintf('[%s] %s', $record['channel'], $record['message']);
        
        syslog($priority, $message);
    }
    
    public function close(): void
    {
        if ($this->opened) {
            closelog();
            $this->opened = false;
        }
    }
}