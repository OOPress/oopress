<?php

declare(strict_types=1);

namespace OOPress\Installer;

use Doctrine\DBAL\Connection;
use OOPress\Extension\ExtensionLoader;
use OOPress\Migration\MigrationRunner;
use OOPress\Path\PathResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Installer — Handles fresh OOPress installations.
 * 
 * This class manages the installation process including:
 * - Database connection verification
 * - Running core migrations
 * - Creating the admin user
 * - Writing configuration files
 * - Setting up the filesystem structure
 * 
 * @api
 */
class Installer
{
    private InstallerState $state;
    private array $errors = [];
    private array $warnings = [];
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly Connection $connection,
        private readonly MigrationRunner $migrationRunner,
        private readonly ExtensionLoader $extensionLoader,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->state = new InstallerState();
    }
    
    /**
     * Check if OOPress is already installed.
     */
    public function isInstalled(): bool
    {
        // Check if settings.php exists
        if (file_exists($this->pathResolver->getSettingsFile())) {
            return true;
        }
        
        // Check if migrations table exists with any migrations
        try {
            $schemaManager = $this->connection->createSchemaManager();
            if ($schemaManager->tablesExist(['oop_migrations'])) {
                $result = $this->connection->executeQuery(
                    'SELECT COUNT(*) FROM oop_migrations'
                );
                $count = (int) $result->fetchOne();
                
                if ($count > 0) {
                    return true; // Migrations have been run
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist or connection failed — not installed
            return false;
        }
        
        return false;
    }
    
    /**
     * Run the installation process.
     * 
     * @param InstallerConfig $config Installation configuration
     * @return InstallerResult
     */
    public function install(InstallerConfig $config): InstallerResult
    {
        if ($this->isInstalled()) {
            return InstallerResult::failure(
                'OOPress is already installed. Refusing to proceed.'
            );
        }
        
        $this->state->start();
        
        try {
            // Step 1: Validate requirements
            $this->validateRequirements($config);
            if ($this->hasErrors()) {
                return $this->createFailureResult('Requirements validation failed');
            }
            
            // Step 2: Test database connection
            $this->testDatabaseConnection();
            if ($this->hasErrors()) {
                return $this->createFailureResult('Database connection failed');
            }
            
            // Step 3: Create directory structure
            $this->createDirectoryStructure();
            if ($this->hasErrors()) {
                return $this->createFailureResult('Directory creation failed');
            }
            
            // Step 4: Run core migrations
            $this->runMigrations();
            if ($this->hasErrors()) {
                return $this->createFailureResult('Migration failed');
            }
            
            // Step 5: Create admin user
            $userId = $this->createAdminUser($config);
            if ($this->hasErrors()) {
                return $this->createFailureResult('Admin user creation failed');
            }
            
            // Step 6: Install core modules
            $this->installCoreModules();
            if ($this->hasErrors()) {
                return $this->createFailureResult('Core module installation failed');
            }
            
            // Step 7: Write configuration files
            $this->writeConfiguration($config);
            if ($this->hasErrors()) {
                return $this->createFailureResult('Configuration writing failed');
            }
            
            // Step 8: Set up filesystem permissions
            $this->setPermissions();
            if ($this->hasErrors()) {
                // Permissions warnings don't stop installation
                $this->addWarning('Permission setting had issues. Some features may not work.');
            }
            
            $this->state->complete();
            
            return InstallerResult::success(
                'OOPress installed successfully',
                $this->state,
                $userId
            );
            
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
            return $this->createFailureResult($e->getMessage());
        }
    }
    
    /**
     * Validate installation requirements.
     */
    private function validateRequirements(InstallerConfig $config): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            $this->addError(sprintf(
                'PHP 8.2+ is required. Current version: %s',
                PHP_VERSION
            ));
        }
        
        // Check required extensions
        $requiredExtensions = ['pdo', 'json', 'mbstring', 'session', 'ctype'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->addError(sprintf(
                    'Required PHP extension missing: %s',
                    $ext
                ));
            }
        }
        
        // Check filesystem permissions
        $writablePaths = [
            $this->pathResolver->getVarPath(),
            $this->pathResolver->getFilesPath(),
            dirname($this->pathResolver->getSettingsFile()),
        ];
        
        foreach ($writablePaths as $path) {
            if (!is_writable($path)) {
                $this->addError(sprintf(
                    'Path is not writable: %s',
                    $path
                ));
            }
        }
        
        // Validate admin password
        if (strlen($config->adminPassword) < 8) {
            $this->addError('Admin password must be at least 8 characters');
        }
        
        // Validate email
        if (!filter_var($config->adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->addError('Invalid admin email address');
        }
    }
    
    /**
     * Test database connection.
     */
    private function testDatabaseConnection(): void
    {
        try {
            $this->connection->connect();
            
            // Check if we can execute a simple query
            $this->connection->executeQuery('SELECT 1');
            
            $this->state->addStep('database_connection', true);
        } catch (\Exception $e) {
            $this->addError(sprintf(
                'Database connection failed: %s',
                $e->getMessage()
            ));
            $this->state->addStep('database_connection', false, $e->getMessage());
        }
    }
    
    /**
     * Create required directory structure.
     */
    private function createDirectoryStructure(): void
    {
        $directories = [
            $this->pathResolver->getVarPath() . '/cache',
            $this->pathResolver->getVarPath() . '/log',
            $this->pathResolver->getVarPath() . '/sessions',
            $this->pathResolver->getFilesPath(),
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $this->addError(sprintf(
                        'Failed to create directory: %s',
                        $dir
                    ));
                    continue;
                }
            }
            
            $this->state->addStep('create_directory_' . basename($dir), true);
        }
    }
    
    /**
     * Run database migrations.
     */
    private function runMigrations(): void
    {
        try {
            $result = $this->migrationRunner->migrate();
            
            if ($result->success) {
                $this->state->addStep('migrations', true, null, $result->migrationsExecuted);
            } else {
                $this->addError(sprintf(
                    'Migration failed: %s',
                    $result->getErrorMessage()
                ));
                $this->state->addStep('migrations', false, $result->getErrorMessage());
            }
        } catch (\Exception $e) {
            $this->addError(sprintf(
                'Migration runner exception: %s',
                $e->getMessage()
            ));
            $this->state->addStep('migrations', false, $e->getMessage());
        }
    }
    
    /**
     * Create the admin user.
     */
    private function createAdminUser(InstallerConfig $config): int
    {
        try {
            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword(
                new AdminUser(),
                $config->adminPassword
            );
            
            // Insert the user
            $this->connection->insert('oop_users', [
                'username' => $config->adminUsername,
                'email' => $config->adminEmail,
                'password' => $hashedPassword,
                'status' => 'active',
                'roles' => json_encode(['ROLE_ADMIN', 'ROLE_USER']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            $userId = (int) $this->connection->lastInsertId();
            
            $this->state->addStep('admin_user', true, null, $userId);
            $this->state->setAdminUserId($userId);
            
            return $userId;
            
        } catch (\Exception $e) {
            $this->addError(sprintf(
                'Failed to create admin user: %s',
                $e->getMessage()
            ));
            $this->state->addStep('admin_user', false, $e->getMessage());
            
            throw $e;
        }
    }
    
    /**
     * Install core modules.
     */
    private function installCoreModules(): void
    {
        $coreModules = [
            'oopress/dashboard',
            'oopress/users',
            'oopress/media',
            'oopress/search',
        ];
        
        foreach ($coreModules as $moduleId) {
            $module = $this->extensionLoader->getModule($moduleId);
            
            if (!$module) {
                $this->addWarning(sprintf(
                    'Core module not found: %s',
                    $moduleId
                ));
                continue;
            }
            
            // In v1, module "installation" is just marking them as enabled
            // This will be expanded when we have module state management
            $this->state->addStep('module_' . $moduleId, true);
        }
    }
    
    /**
     * Write configuration files.
     */
    private function writeConfiguration(InstallerConfig $config): void
    {
        $settingsFile = $this->pathResolver->getSettingsFile();
        
        // Don't overwrite existing settings.php
        if (file_exists($settingsFile)) {
            $this->addWarning('settings.php already exists. Skipping write.');
            return;
        }
        
        $content = $this->generateSettingsContent($config);
        
        if (file_put_contents($settingsFile, $content) === false) {
            $this->addError(sprintf(
                'Failed to write settings.php to: %s',
                $settingsFile
            ));
            return;
        }
        
        // Set restrictive permissions
        chmod($settingsFile, 0640);
        
        $this->state->addStep('configuration', true);
    }
    
    /**
     * Generate the settings.php file content.
     */
    private function generateSettingsContent(InstallerConfig $config): string
    {
        return <<<PHP
<?php

/**
 * OOPress Configuration File
 * 
 * This file contains sensitive credentials and site-specific settings.
 * It is excluded from version control and should never be committed.
 * 
 * Generated: {$this->state->getStartTime()->format('Y-m-d H:i:s')}
 */

// Database Configuration
\$config['database'] = [
    'driver' => '{$config->dbDriver}',
    'host' => '{$config->dbHost}',
    'port' => {$config->dbPort},
    'dbname' => '{$config->dbName}',
    'user' => '{$config->dbUser}',
    'password' => '{$config->dbPassword}',
    'charset' => 'utf8mb4',
];

// Site Configuration
\$config['site'] = [
    'name' => '{$config->siteName}',
    'url' => '{$config->siteUrl}',
    'language' => '{$config->language}',
    'timezone' => '{$config->timezone}',
];

// Security
\$config['security'] = [
    'secret_key' => '{$this->generateSecretKey()}',
    'install_token' => '{$this->generateInstallToken()}',
];

// Installation Record
\$config['install'] = [
    'version' => '1.0.0',
    'installed' => '{$this->state->getStartTime()->format('Y-m-d H:i:s')}',
];

// Performance
\$config['performance'] = [
    'cache_enabled' => true,
    'cache_ttl' => 3600,
];

return \$config;
PHP;
    }
    
    /**
     * Generate a random secret key.
     */
    private function generateSecretKey(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Generate an installation token for future updates.
     */
    private function generateInstallToken(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Set filesystem permissions.
     */
    private function setPermissions(): void
    {
        // Set var/ to be writable by web server
        $varPath = $this->pathResolver->getVarPath();
        if (is_dir($varPath)) {
            chmod($varPath, 0755);
            
            // Recursively set subdirectories
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($varPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    chmod($file->getPathname(), 0755);
                } else {
                    chmod($file->getPathname(), 0644);
                }
            }
        }
        
        // Set files/ to be writable
        $filesPath = $this->pathResolver->getFilesPath();
        if (is_dir($filesPath)) {
            chmod($filesPath, 0755);
        }
        
        $this->state->addStep('permissions', true);
    }
    
    private function addError(string $error): void
    {
        $this->errors[] = $error;
        $this->state->addError($error);
    }
    
    private function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
        $this->state->addWarning($warning);
    }
    
    private function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    private function createFailureResult(string $message): InstallerResult
    {
        return InstallerResult::failure($message, $this->state, $this->errors);
    }
}

// Temporary class for password hashing
// This will be replaced with the actual User entity when built
class AdminUser
{
    public function getId(): ?int { return null; }
    public function getPassword(): ?string { return null; }
    public function getSalt(): ?string { return null; }
    public function getRoles(): array { return ['ROLE_ADMIN']; }
    public function eraseCredentials(): void {}
}
