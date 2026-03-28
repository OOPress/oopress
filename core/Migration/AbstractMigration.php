<?php

declare(strict_types=1);

namespace OOPress\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration as DoctrineAbstractMigration;

/**
 * AbstractMigration — Base class for all OOPress migrations.
 * 
 * This extends Doctrine's AbstractMigration to provide OOPress-specific
 * functionality while maintaining full compatibility with the migration runner.
 * 
 * @internal — Migration classes are internal to the migration system.
 *             Module authors should extend this class for their own migrations.
 */
abstract class AbstractMigration extends DoctrineAbstractMigration
{
    /**
     * Get the migration version number from the class name.
     * 
     * Migration classes should follow the format: VersionYYYYMMDDHHMMSS
     * Example: Version20260328120000
     * 
     * @return string The version number (timestamp portion)
     */
    public function getVersionNumber(): string
    {
        if (!preg_match('/Version(\d+)/', static::class, $matches)) {
            throw new \RuntimeException(
                sprintf('Invalid migration class name: %s. Must follow pattern VersionYYYYMMDDHHMMSS', static::class)
            );
        }
        
        return $matches[1];
    }
    
    /**
     * Check if this migration has been executed.
     * 
     * @param Connection $connection
     * @return bool
     */
    protected function isExecuted(Connection $connection): bool
    {
        $schemaManager = $connection->createSchemaManager();
        
        if (!$schemaManager->tablesExist(['oop_migrations'])) {
            return false;
        }
        
        $result = $connection->executeQuery(
            'SELECT 1 FROM oop_migrations WHERE version = :version',
            ['version' => $this->getVersionNumber()]
        );
        
        return $result->fetchOne() !== false;
    }
    
    /**
     * Log a migration message.
     * 
     * @param string $message
     */
    protected function log(string $message): void
    {
        $this->write(sprintf('[%s] %s', $this->getVersionNumber(), $message));
    }
    
    /**
     * Check if a table exists.
     * 
     * @param Connection $connection
     * @param string $table
     * @return bool
     */
    protected function tableExists(Connection $connection, string $table): bool
    {
        $schemaManager = $connection->createSchemaManager();
        return $schemaManager->tablesExist([$table]);
    }
    
    /**
     * Get the prefixed table name.
     * 
     * @param Connection $connection
     * @param string $table
     * @return string
     */
    protected function getPrefixedTableName(Connection $connection, string $table): string
    {
        // This will be enhanced when we have table prefix configuration
        // For now, assume 'oop_' prefix
        $prefix = 'oop_';
        
        // Remove existing prefix if present
        if (str_starts_with($table, $prefix)) {
            $table = substr($table, strlen($prefix));
        }
        
        return $prefix . $table;
    }
}
