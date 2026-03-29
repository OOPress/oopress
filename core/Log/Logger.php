<?php

declare(strict_types=1);

namespace OOPress\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use OOPress\Event\HookDispatcher;

/**
 * Logger — PSR-3 compliant logger.
 * 
 * GDPR compliant: All logs stored locally. No external services.
 * 
 * @api
 */
class Logger extends AbstractLogger
{
    private array $channels = [];
    private array $processors = [];
    private array $handlers = [];
    private string $minLevel;
    
    public function __construct(
        private readonly HookDispatcher $hookDispatcher,
        array $config = [],
    ) {
        $this->minLevel = $config['min_level'] ?? LogLevel::INFO;
        $this->initializeChannels($config);
        $this->initializeHandlers($config);
        
        // Dispatch event for custom logging configuration
        $event = new Event\LoggerInitEvent($this);
        $this->hookDispatcher->dispatch($event, 'logger.init');
    }
    
    /**
     * Initialize log channels.
     */
    private function initializeChannels(array $config): void
    {
        $channels = $config['channels'] ?? ['default'];
        
        foreach ($channels as $channel) {
            $this->channels[$channel] = [];
        }
    }
    
    /**
     * Initialize log handlers.
     */
    private function initializeHandlers(array $config): void
    {
        $handlers = $config['handlers'] ?? ['file'];
        
        foreach ($handlers as $handler) {
            switch ($handler) {
                case 'file':
                    $this->handlers[] = new Handler\FileHandler($config['file'] ?? []);
                    break;
                case 'database':
                    $this->handlers[] = new Handler\DatabaseHandler($config['database'] ?? []);
                    break;
                case 'syslog':
                    $this->handlers[] = new Handler\SyslogHandler($config['syslog'] ?? []);
                    break;
                case 'null':
                    $this->handlers[] = new Handler\NullHandler();
                    break;
            }
        }
    }
    
    /**
     * Add a log processor.
     */
    public function addProcessor(callable $processor): void
    {
        $this->processors[] = $processor;
    }
    
    /**
     * Add a log handler.
     */
    public function addHandler(Handler\HandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }
    
    /**
     * Log a message.
     */
    public function log($level, $message, array $context = []): void
    {
        // Check minimum level
        if (!$this->isLevelLoggable($level)) {
            return;
        }
        
        // Create log record
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'channel' => $context['channel'] ?? 'default',
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'memory' => memory_get_usage(),
            'pid' => getmypid(),
        ];
        
        // Add request context if available
        $this->addRequestContext($record);
        
        // Process record through processors
        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }
        
        // Handle the record
        foreach ($this->handlers as $handler) {
            if ($handler->isHandling($level)) {
                $handler->handle($record);
            }
        }
        
        // Dispatch log event
        $event = new Event\LogEvent($record);
        $this->hookDispatcher->dispatch($event, 'logger.log');
    }
    
    /**
     * Add request context to log record.
     */
    private function addRequestContext(array &$record): void
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $record['request_uri'] = $_SERVER['REQUEST_URI'];
            $record['request_method'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $record['ip'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $record['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        } else {
            $record['request_uri'] = 'cli';
            $record['request_method'] = 'CLI';
        }
    }
    
    /**
     * Check if a level is loggable.
     */
    private function isLevelLoggable(string $level): bool
    {
        $levels = [
            LogLevel::DEBUG => 0,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 3,
            LogLevel::ERROR => 4,
            LogLevel::CRITICAL => 5,
            LogLevel::ALERT => 6,
            LogLevel::EMERGENCY => 7,
        ];
        
        $min = $levels[$this->minLevel] ?? 0;
        $current = $levels[$level] ?? 0;
        
        return $current >= $min;
    }
    
    /**
     * Create a child logger for a specific channel.
     */
    public function channel(string $channel): self
    {
        $logger = clone $this;
        $logger->addProcessor(function($record) use ($channel) {
            $record['channel'] = $channel;
            return $record;
        });
        
        return $logger;
    }
}