<?php

declare(strict_types=1);

namespace OOPress\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use OOPress\Kernel;

/**
 * Base test case for OOPress tests.
 * 
 * @internal
 */
abstract class TestCase extends BaseTestCase
{
    protected static ?Kernel $kernel = null;
    protected static ?Connection $connection = null;
    protected static array $testData = [];
    
    /**
     * Set up before test class.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        // Initialize kernel in test mode
        static::$kernel = new Kernel(
            projectRoot: dirname(__DIR__),
            environment: 'test',
            debug: true
        );
        static::$kernel->boot();
        
        // Set up test database
        static::setupTestDatabase();
    }
    
    /**
     * Tear down after test class.
     */
    public static function tearDownAfterClass(): void
    {
        static::cleanupTestDatabase();
        
        if (static::$kernel) {
            static::$kernel->shutdown();
        }
        
        parent::tearDownAfterClass();
    }
    
    /**
     * Set up test database.
     */
    protected static function setupTestDatabase(): void
    {
        $config = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];
        
        static::$connection = DriverManager::getConnection($config);
        
        // Run migrations
        static::runMigrations();
    }
    
    /**
     * Run database migrations.
     */
    protected static function runMigrations(): void
    {
        $migrationFiles = glob(dirname(__DIR__) . '/core/Migration/Migrations/*.php');
        
        foreach ($migrationFiles as $file) {
            require_once $file;
            $className = 'OOPress\\Migration\\Migrations\\' . pathinfo($file, PATHINFO_FILENAME);
            
            if (class_exists($className)) {
                $migration = new $className();
                $schema = static::$connection->createSchemaManager()->createSchema();
                $migration->up($schema);
                
                foreach ($schema->toSql(static::$connection->getDatabasePlatform()) as $sql) {
                    static::$connection->executeStatement($sql);
                }
            }
        }
    }
    
    /**
     * Clean up test database.
     */
    protected static function cleanupTestDatabase(): void
    {
        if (static::$connection) {
            $schemaManager = static::$connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            
            foreach ($tables as $table) {
                static::$connection->executeStatement("DROP TABLE $table");
            }
            
            static::$connection->close();
        }
    }
    
    /**
     * Set up before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::beginTransaction();
    }
    
    /**
     * Tear down after each test.
     */
    protected function tearDown(): void
    {
        static::rollbackTransaction();
        parent::tearDown();
    }
    
    /**
     * Begin database transaction.
     */
    protected static function beginTransaction(): void
    {
        if (static::$connection) {
            static::$connection->beginTransaction();
        }
    }
    
    /**
     * Rollback database transaction.
     */
    protected static function rollbackTransaction(): void
    {
        if (static::$connection && static::$connection->isTransactionActive()) {
            static::$connection->rollBack();
        }
    }
    
    /**
     * Get service from container.
     */
    protected function getService(string $serviceId): object
    {
        return static::$kernel->getContainer()->get($serviceId);
    }
    
    /**
     * Create a mock service.
     */
    protected function createMockService(string $className): object
    {
        return $this->createMock($className);
    }
    
    /**
     * Assert response is successful.
     */
    protected function assertSuccess(array $response): void
    {
        $this->assertTrue($response['success'] ?? false);
    }
    
    /**
     * Assert response has error.
     */
    protected function assertError(array $response, string $expectedMessage = null): void
    {
        $this->assertFalse($response['success'] ?? true);
        
        if ($expectedMessage !== null) {
            $this->assertStringContainsString($expectedMessage, $response['message'] ?? '');
        }
    }
    
    /**
     * Create test content data.
     */
    protected function createTestContentData(array $overrides = []): array
    {
        return array_merge([
            'content_type' => 'article',
            'title' => 'Test Article',
            'body' => 'This is a test article body.',
            'language' => 'en',
            'status' => 'published',
        ], $overrides);
    }
    
    /**
     * Create test user data.
     */
    protected function createTestUserData(array $overrides = []): array
    {
        return array_merge([
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => 'TestPassword123!',
            'roles' => ['ROLE_USER'],
        ], $overrides);
    }
}