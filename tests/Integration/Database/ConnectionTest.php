<?php

declare(strict_types=1);

namespace OOPress\Tests\Integration\Database;

use OOPress\Tests\TestCase;

/**
 * Test database connection and operations.
 * 
 * @internal
 */
class ConnectionTest extends TestCase
{
    public function testConnectionIsAvailable(): void
    {
        $connection = static::$connection;
        
        $this->assertNotNull($connection);
        $this->assertTrue($connection->isConnected());
    }
    
    public function testExecuteQuery(): void
    {
        $result = static::$connection->executeQuery('SELECT 1');
        
        $this->assertNotNull($result);
        $this->assertEquals(1, $result->fetchOne());
    }
    
    public function testCreateAndDropTable(): void
    {
        $schemaManager = static::$connection->createSchemaManager();
        $tableName = 'test_table';
        
        // Create table
        $schemaManager->createTable(
            $schemaManager->createSchemaConfig()->createTable($tableName)
                ->addColumn('id', 'integer', ['autoincrement' => true])
                ->addColumn('name', 'string', ['length' => 255])
                ->setPrimaryKey(['id'])
        );
        
        $this->assertTrue($schemaManager->tablesExist([$tableName]));
        
        // Drop table
        $schemaManager->dropTable($tableName);
        
        $this->assertFalse($schemaManager->tablesExist([$tableName]));
    }
    
    public function testInsertAndSelect(): void
    {
        $schemaManager = static::$connection->createSchemaManager();
        $tableName = 'test_users';
        
        // Create table
        $schemaManager->createTable(
            $schemaManager->createSchemaConfig()->createTable($tableName)
                ->addColumn('id', 'integer', ['autoincrement' => true])
                ->addColumn('username', 'string', ['length' => 255])
                ->addColumn('email', 'string', ['length' => 255])
                ->setPrimaryKey(['id'])
        );
        
        // Insert data
        static::$connection->insert($tableName, [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
        
        // Select data
        $result = static::$connection->fetchAssociative(
            "SELECT * FROM $tableName WHERE username = 'testuser'"
        );
        
        $this->assertNotEmpty($result);
        $this->assertEquals('testuser', $result['username']);
        $this->assertEquals('test@example.com', $result['email']);
        
        // Clean up
        $schemaManager->dropTable($tableName);
    }
    
    public function testTransactionRollback(): void
    {
        $schemaManager = static::$connection->createSchemaManager();
        $tableName = 'test_transaction';
        
        // Create table
        $schemaManager->createTable(
            $schemaManager->createSchemaConfig()->createTable($tableName)
                ->addColumn('id', 'integer', ['autoincrement' => true])
                ->addColumn('value', 'string', ['length' => 255])
                ->setPrimaryKey(['id'])
        );
        
        // Start transaction
        static::$connection->beginTransaction();
        
        static::$connection->insert($tableName, ['value' => 'test1']);
        static::$connection->insert($tableName, ['value' => 'test2']);
        
        // Rollback
        static::$connection->rollBack();
        
        // Check that no data was inserted
        $count = static::$connection->fetchOne("SELECT COUNT(*) FROM $tableName");
        $this->assertEquals(0, $count);
        
        // Clean up
        $schemaManager->dropTable($tableName);
    }
}