<?php

declare(strict_types=1);

namespace OOPress\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use OOPress\Path\PathResolver;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * MigrationRunner — Executes database migrations.
 * 
 * All update paths (web UI, Composer, CLI) funnel through this class.
 * It ensures migrations are executed consistently regardless of how
 * files were delivered.
 * 
 * @api — This is a public contract for the migration system.
 */
class MigrationRunner
{
    private DependencyFactory $dependencyFactory;
    private Configuration $configuration;
    
    public function __construct(
        private readonly Connection $connection,
        private readonly PathResolver $pathResolver,
    ) {
        $this->initializeConfiguration();
    }
    
    /**
     * Initialize the Doctrine Migrations configuration.
     */
    private function initializeConfiguration(): void
    {
        // Create migration configuration
        $this->configuration = new Configuration();
        
        // Set migration directory
        $migrationsPath = $this->pathResolver->getCorePath() . '/Migration/Migrations';
        $this->configuration->addMigrationsDirectory('OOPress\\Migration\\Migrations', $migrationsPath);
        
        // Set namespace for generated migrations
        $this->configuration->setMigrationsNamespace('OOPress\\Migration\\Migrations');
        
        // Configure metadata storage
        $storageConfig = new TableMetadataStorageConfiguration();
        $storageConfig->setTableName('oop_migrations');
        $this->configuration->setMetadataStorageConfiguration($storageConfig);
        
        // Set whether migrations are organized by year
        $this->configuration->setAllOrNothing(true);
        $this->configuration->setCheckDatabasePlatform(false);
        
        // Create dependency factory
        $this->dependencyFactory = DependencyFactory::fromConnection(
            new ExistingConfiguration($this->configuration),
            new ExistingConnection($this->connection)
        );
    }
    
    /**
     * Register module migrations.
     * 
     * Modules can provide their own migrations by placing them in:
     * modules/{module_id}/Migrations/
     * 
     * @param string $moduleId The module ID (e.g., "oopress/users")
     * @param string $namespace The namespace for the module's migrations
     * @throws \RuntimeException if migrations directory doesn't exist
     */
    public function registerModuleMigrations(string $moduleId, string $namespace): void
    {
        $modulePath = $this->pathResolver->getModulePath($moduleId);
        $migrationsPath = $modulePath . '/Migrations';
        
        if (!is_dir($migrationsPath)) {
            return; // No migrations for this module, that's fine
        }
        
        $this->configuration->addMigrationsDirectory($namespace, $migrationsPath);
    }
    
    /**
     * Get the current migration version.
     * 
     * @return string|null The current version, or null if no migrations run
     */
    public function getCurrentVersion(): ?string
    {
        $command = new StatusCommand($this->dependencyFactory);
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        
        try {
            // This is a bit hacky, but Doctrine's status command gives us the info
            $command->run($input, $output);
            $content = $output->fetch();
            
            if (preg_match('/Current Version:\s*(\d+)/', $content, $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            // No migrations table yet
            return null;
        }
        
        return null;
    }
    
    /**
     * Check if all migrations are up to date.
     * 
     * @return bool
     */
    public function isUpToDate(): bool
    {
        $command = new UpToDateCommand($this->dependencyFactory);
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        
        try {
            $command->run($input, $output);
            $content = $output->fetch();
            
            // Doctrine returns exit code 0 when up to date, but we don't have exit codes here
            // So we parse the output
            return str_contains($content, 'Up-to-date') && !str_contains($content, 'Out-of-date');
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Run all pending migrations.
     * 
     * @param bool $dryRun If true, simulate without making changes
     * @return MigrationResult The result of the migration run
     */
    public function migrate(bool $dryRun = false): MigrationResult
    {
        $command = new MigrateCommand($this->dependencyFactory);
        
        $input = new ArrayInput([
            '--dry-run' => $dryRun,
            '--allow-no-migration' => true,
        ]);
        
        $output = new BufferedOutput();
        
        try {
            $exitCode = $command->run($input, $output);
            $outputContent = $output->fetch();
            
            return new MigrationResult(
                success: $exitCode === 0,
                output: $outputContent,
                migrationsExecuted: $this->parseMigrationsExecuted($outputContent),
                dryRun: $dryRun,
            );
        } catch (\Exception $e) {
            return new MigrationResult(
                success: false,
                output: $output->fetch(),
                error: $e->getMessage(),
                dryRun: $dryRun,
            );
        }
    }
    
    /**
     * Migrate to a specific version.
     * 
     * @param string $version The version to migrate to
     * @param bool $dryRun If true, simulate without making changes
     * @return MigrationResult
     */
    public function migrateTo(string $version, bool $dryRun = false): MigrationResult
    {
        $command = new MigrateCommand($this->dependencyFactory);
        
        $input = new ArrayInput([
            'version' => $version,
            '--dry-run' => $dryRun,
        ]);
        
        $output = new BufferedOutput();
        
        try {
            $exitCode = $command->run($input, $output);
            $outputContent = $output->fetch();
            
            return new MigrationResult(
                success: $exitCode === 0,
                output: $outputContent,
                migrationsExecuted: $this->parseMigrationsExecuted($outputContent),
                dryRun: $dryRun,
            );
        } catch (\Exception $e) {
            return new MigrationResult(
                success: false,
                output: $output->fetch(),
                error: $e->getMessage(),
                dryRun: $dryRun,
            );
        }
    }
    
    /**
     * Parse the number of migrations executed from output.
     */
    private function parseMigrationsExecuted(string $output): int
    {
        if (preg_match('/(\d+)\s+migrations? executed/', $output, $matches)) {
            return (int) $matches[1];
        }
        
        return 0;
    }
    
    /**
     * Get the list of available migrations.
     * 
     * @return array<string, string> Version => migration class name
     */
    public function getAvailableMigrations(): array
    {
        $migrations = $this->dependencyFactory->getMigrationRepository()->getMigrations();
        $result = [];
        
        foreach ($migrations as $migration) {
            $result[$migration->getVersion()] = $migration->getMigrationClass();
        }
        
        return $result;
    }
    
    /**
     * Get the list of executed migrations.
     * 
     * @return array<string> Executed migration versions
     */
    public function getExecutedMigrations(): array
    {
        try {
            $executed = $this->dependencyFactory->getMetadataStorage()->getExecutedMigrations();
            $result = [];
            
            foreach ($executed as $migration) {
                $result[] = $migration->getVersion();
            }
            
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Mark a migration as executed without running it.
     * 
     * Useful for manual intervention or when migrations are applied outside
     * the migration system.
     * 
     * @param string $version
     * @return bool
     */
    public function markExecuted(string $version): bool
    {
        $command = new VersionCommand($this->dependencyFactory);
        
        $input = new ArrayInput([
            'version' => $version,
            '--add' => true,
        ]);
        
        $output = new BufferedOutput();
        
        try {
            $exitCode = $command->run($input, $output);
            return $exitCode === 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
