<?php

declare(strict_types=1);

namespace OOPress\Core\Database;

use Medoo\Medoo;

class Migration
{
    private Medoo $db;
    private string $migrationTable = 'migrations';
    
    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->ensureMigrationTable();
    }
    
    private function ensureMigrationTable(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function run(string $migrationsPath): void
    {
        $executed = $this->getExecutedMigrations();
        $files = glob($migrationsPath . '/*.php');
        $batch = $this->getNextBatchNumber();
        
        foreach ($files as $file) {
            $migration = basename($file, '.php');
            
            if (in_array($migration, $executed)) {
                continue;
            }
            
            $instance = require $file;
            if (method_exists($instance, 'up')) {
                $instance->up($this->db);
                $this->recordMigration($migration, $batch);
                echo "✓ Migrated: {$migration}\n";
            }
        }
    }
    
    public function rollback(int $steps = 1): void
    {
        $batches = $this->getLastBatches($steps);
        
        foreach ($batches as $batch) {
            $migrations = $this->getMigrationsByBatch($batch);
            
            foreach (array_reverse($migrations) as $migration) {
                $file = __DIR__ . "/../../../database/migrations/{$migration}.php";
                if (file_exists($file)) {
                    $instance = require $file;
                    if (method_exists($instance, 'down')) {
                        $instance->down($this->db);
                        $this->removeMigration($migration);
                        echo "✓ Rolled back: {$migration}\n";
                    }
                }
            }
        }
    }
    
    private function getExecutedMigrations(): array
    {
        // Check if table exists first
        $tableExists = $this->db->query("SHOW TABLES LIKE '{$this->migrationTable}'")->rowCount() > 0;
        
        if (!$tableExists) {
            return [];
        }
        
        $results = $this->db->select($this->migrationTable, 'migration');
        return array_column($results, 'migration');
    }
    
    private function getNextBatchNumber(): int
    {
        $max = $this->db->max($this->migrationTable, 'batch');
        // If no records exist, start at batch 1
        if (empty($max)) {
            return 1;
        }
        // Force to integer and add 1
        return intval($max) + 1;
    }
    
    private function getLastBatches(int $steps): array
    {
        $results = $this->db->select($this->migrationTable, 'batch', [
            'GROUP' => 'batch',
            'ORDER' => ['batch' => 'DESC']
        ]);
        
        $batches = array_column($results, 'batch');
        $batches = array_map('intval', $batches);
        return array_slice($batches, 0, $steps);
    }
    
    private function getMigrationsByBatch(int $batch): array
    {
        $results = $this->db->select($this->migrationTable, 'migration', [
            'batch' => $batch
        ]);
        
        return array_column($results, 'migration');
    }
    
    private function recordMigration(string $migration, int $batch): void
    {
        $this->db->insert($this->migrationTable, [
            'migration' => $migration,
            'batch' => $batch
        ]);
    }
    
    private function removeMigration(string $migration): void
    {
        $this->db->delete($this->migrationTable, [
            'migration' => $migration
        ]);
    }
}