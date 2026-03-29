<?php

declare(strict_types=1);

namespace OOPress\Log\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LogLevel;

/**
 * DatabaseHandler — Writes logs to database.
 * 
 * GDPR compliant: Logs stored in local database.
 * 
 * @api
 */
class DatabaseHandler implements HandlerInterface
{
    private ?Connection $connection;
    private string $table;
    private string $level;
    private array $levels = [];
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->table = $config['table'] ?? 'oop_logs';
        $this->level = $config['level'] ?? LogLevel::DEBUG;
        
        $this->initializeLevels();
        $this->initializeConnection();
        $this->ensureTableExists();
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
    
    /**
     * Initialize database connection.
     */
    private function initializeConnection(): void
    {
        try {
            // Try to get connection from container if available
            if (isset($GLOBALS['container']) && $GLOBALS['container']->has(Connection::class)) {
                $this->connection = $GLOBALS['container']->get(Connection::class);
                return;
            }
        } catch (\Exception $e) {
            // Fall back to config
        }
        
        // Create connection from config
        if (isset($this->config['connection'])) {
            $this->connection = DriverManager::getConnection($this->config['connection']);
        }
    }
    
    /**
     * Ensure logs table exists.
     */
    private function ensureTableExists(): void
    {
        if (!$this->connection) {
            return;
        }
        
        $schemaManager = $this->connection->createSchemaManager();
        
        if ($schemaManager->tablesExist([$this->table])) {
            return;
        }
        
        $schemaManager->createTable(
            $schemaManager->createSchemaConfig()->createTable($this->table)
                ->addColumn('id', 'integer', ['autoincrement' => true])
                ->addColumn('level', 'string', ['length' => 20])
                ->addColumn('message', 'text')
                ->addColumn('context', 'text', ['notnull' => false])
                ->addColumn('channel', 'string', ['length' => 50])
                ->addColumn('request_uri', 'string', ['length' => 500, 'notnull' => false])
                ->addColumn('request_method', 'string', ['length' => 10, 'notnull' => false])
                ->addColumn('ip', 'string', ['length' => 45, 'notnull' => false])
                ->addColumn('user_agent', 'string', ['length' => 255, 'notnull' => false])
                ->addColumn('user_id', 'integer', ['notnull' => false])
                ->addColumn('pid', 'integer')
                ->addColumn('memory', 'integer')
                ->addColumn('created_at', 'datetime')
                ->setPrimaryKey(['id'])
                ->addIndex(['level'], 'idx_level')
                ->addIndex(['channel'], 'idx_channel')
                ->addIndex(['created_at'], 'idx_created_at')
                ->addIndex(['ip'], 'idx_ip')
        );
    }
    
    public function isHandling(string $level): bool
    {
        return $this->levels[$level] >= $this->levels[$this->level];
    }
    
    public function handle(array $record): void
    {
        if (!$this->connection) {
            return;
        }
        
        try {
            $this->connection->insert($this->table, [
                'level' => $record['level'],
                'message' => substr($record['message'], 0, 65535),
                'context' => !empty($record['context']) ? json_encode($record['context']) : null,
                'channel' => $record['channel'],
                'request_uri' => $record['request_uri'] ?? null,
                'request_method' => $record['request_method'] ?? null,
                'ip' => $record['ip'] ?? null,
                'user_agent' => $record['user_agent'] ?? null,
                'user_id' => $record['user_id'] ?? null,
                'pid' => $record['pid'],
                'memory' => $record['memory'],
                'created_at' => $record['datetime'],
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't cause errors from logging
        }
    }
    
    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}