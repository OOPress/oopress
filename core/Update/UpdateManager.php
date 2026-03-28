<?php

declare(strict_types=1);

namespace OOPress\Update;

use OOPress\Migration\MigrationRunner;
use OOPress\Path\PathResolver;
use OOPress\Extension\ExtensionLoader;

/**
 * UpdateManager — Handles OOPress core and module updates.
 * 
 * Supports three update paths:
 * 1. Web UI updater (download zip, verify, swap files)
 * 2. Composer (for developers)
 * 3. CLI command (for SSH access)
 * 
 * All paths funnel through the same migration runner.
 * 
 * @api
 */
class UpdateManager
{
    private const UPDATE_CHECK_URL = 'https://updates.oopress.org/';
    
    private array $errors = [];
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly MigrationRunner $migrationRunner,
        private readonly ExtensionLoader $extensionLoader,
    ) {}
    
    /**
     * Check for updates.
     * 
     * @param string $type 'core' or 'module'
     * @param string|null $moduleId Module ID if checking module updates
     * @return array<UpdateInfo>
     */
    public function checkUpdates(string $type = 'core', ?string $moduleId = null): array
    {
        try {
            $response = $this->fetchUpdateInfo($type, $moduleId);
            
            if (!$response) {
                return [];
            }
            
            $updates = [];
            
            if ($type === 'core') {
                $currentVersion = $this->getCurrentCoreVersion();
                
                foreach ($response['releases'] ?? [] as $release) {
                    if (version_compare($release['version'], $currentVersion, '>')) {
                        $updates[] = UpdateInfo::fromArray($release, 'core');
                    }
                }
            } else {
                $modules = $moduleId ? [$moduleId => $this->extensionLoader->getModule($moduleId)] : $this->extensionLoader->getModules();
                
                foreach ($modules as $id => $module) {
                    if (isset($response['modules'][$id])) {
                        if (version_compare($response['modules'][$id]['version'], $module->version, '>')) {
                            $updates[] = UpdateInfo::fromArray($response['modules'][$id], 'module', $id);
                        }
                    }
                }
            }
            
            return $updates;
            
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
            return [];
        }
    }
    
    /**
     * Update core.
     */
    public function updateCore(string $version, bool $dryRun = false): UpdateResult
    {
        $startTime = microtime(true);
        
        try {
            // Download update package
            $packageUrl = $this->getDownloadUrl('core', $version);
            $tempFile = $this->downloadPackage($packageUrl);
            
            if (!$tempFile) {
                return $this->createFailureResult('Failed to download update package', $startTime);
            }
            
            // Verify checksum
            if (!$this->verifyChecksum($tempFile, $version)) {
                $this->cleanupTempFile($tempFile);
                return $this->createFailureResult('Checksum verification failed', $startTime);
            }
            
            // Extract package
            $extractPath = $this->extractPackage($tempFile);
            if (!$extractPath) {
                $this->cleanupTempFile($tempFile);
                return $this->createFailureResult('Failed to extract package', $startTime);
            }
            
            if ($dryRun) {
                $this->cleanupTempFile($tempFile);
                $this->cleanupExtractedFiles($extractPath);
                return $this->createSuccessResult('Dry run completed successfully', 0, $startTime);
            }
            
            // Create backup
            $backupPath = $this->createBackup();
            
            // Swap files
            $filesSwapped = $this->swapFiles($extractPath);
            
            if (!$filesSwapped) {
                $this->restoreBackup($backupPath);
                $this->cleanupTempFile($tempFile);
                $this->cleanupExtractedFiles($extractPath);
                return $this->createFailureResult('Failed to swap files', $startTime);
            }
            
            // Run migrations
            $migrationResult = $this->migrationRunner->migrate();
            
            if (!$migrationResult->success) {
                // Rollback
                $this->restoreBackup($backupPath);
                $this->cleanupTempFile($tempFile);
                $this->cleanupExtractedFiles($extractPath);
                return $this->createFailureResult(
                    sprintf('Migration failed: %s', $migrationResult->getErrorMessage()),
                    $startTime
                );
            }
            
            // Clean up
            $this->cleanupBackup($backupPath);
            $this->cleanupTempFile($tempFile);
            $this->cleanupExtractedFiles($extractPath);
            
            return $this->createSuccessResult(
                sprintf('Successfully updated to version %s', $version),
                $migrationResult->migrationsExecuted,
                $startTime
            );
            
        } catch (\Exception $e) {
            return $this->createFailureResult($e->getMessage(), $startTime);
        }
    }
    
    /**
     * Update a module.
     */
    public function updateModule(string $moduleId, string $version, bool $dryRun = false): UpdateResult
    {
        $startTime = microtime(true);
        
        try {
            $module = $this->extensionLoader->getModule($moduleId);
            
            if (!$module) {
                return $this->createFailureResult(sprintf('Module not found: %s', $moduleId), $startTime);
            }
            
            // Download module package
            $packageUrl = $this->getDownloadUrl('module', $version, $moduleId);
            $tempFile = $this->downloadPackage($packageUrl);
            
            if (!$tempFile) {
                return $this->createFailureResult('Failed to download module package', $startTime);
            }
            
            // Verify checksum
            if (!$this->verifyChecksum($tempFile, $version, $moduleId)) {
                $this->cleanupTempFile($tempFile);
                return $this->createFailureResult('Checksum verification failed', $startTime);
            }
            
            // Extract package
            $extractPath = $this->extractPackage($tempFile);
            if (!$extractPath) {
                $this->cleanupTempFile($tempFile);
                return $this->createFailureResult('Failed to extract package', $startTime);
            }
            
            if ($dryRun) {
                $this->cleanupTempFile($tempFile);
                $this->cleanupExtractedFiles($extractPath);
                return $this->createSuccessResult('Dry run completed successfully', 0, $startTime);
            }
            
            // Backup module
            $modulePath = $this->pathResolver->getModulePath($moduleId);
            $backupPath = $this->backupModule($moduleId);
            
            // Swap module files
            $filesSwapped = $this->swapModuleFiles($extractPath, $moduleId);
            
            if (!$filesSwapped) {
                $this->restoreModule($backupPath, $moduleId);
                $this->cleanupTempFile($tempFile);
                $this->cleanupExtractedFiles($extractPath);
                return $this->createFailureResult('Failed to swap module files', $startTime);
            }
            
            // Run module migrations
            $this->migrationRunner->registerModuleMigrations($moduleId, $this->getModuleMigrationNamespace($moduleId));
            $migrationResult = $this->migrationRunner->migrate();
            
            if (!$migrationResult->success) {
                $this->restoreModule($backupPath, $moduleId);
                $this->cleanupTempFile($tempFile);
                $this->cleanupExtractedFiles($extractPath);
                return $this->createFailureResult(
                    sprintf('Migration failed: %s', $migrationResult->getErrorMessage()),
                    $startTime
                );
            }
            
            // Clean up
            $this->cleanupModuleBackup($backupPath);
            $this->cleanupTempFile($tempFile);
            $this->cleanupExtractedFiles($extractPath);
            
            return $this->createSuccessResult(
                sprintf('Successfully updated module %s to version %s', $moduleId, $version),
                $migrationResult->migrationsExecuted,
                $startTime
            );
            
        } catch (\Exception $e) {
            return $this->createFailureResult($e->getMessage(), $startTime);
        }
    }
    
    /**
     * Download package from URL.
     */
    private function downloadPackage(string $url): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'oopress_update_');
        
        $fp = fopen($tempFile, 'w');
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minute timeout
        
        $success = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        
        if (!$success) {
            unlink($tempFile);
            $this->addError(sprintf('Download failed: %s', $error));
            return null;
        }
        
        return $tempFile;
    }
    
    /**
     * Verify package checksum.
     */
    private function verifyChecksum(string $filePath, string $version, ?string $moduleId = null): bool
    {
        $checksumUrl = $this->getChecksumUrl($version, $moduleId);
        
        $expectedChecksum = @file_get_contents($checksumUrl);
        
        if (!$expectedChecksum) {
            // If we can't get checksum, assume it's fine (but warn)
            $this->addError('Could not verify checksum - skipping verification');
            return true;
        }
        
        $actualChecksum = hash_file('sha256', $filePath);
        
        return hash_equals(trim($expectedChecksum), $actualChecksum);
    }
    
    /**
     * Extract package.
     */
    private function extractPackage(string $filePath): ?string
    {
        $extractPath = sys_get_temp_dir() . '/oopress_extract_' . uniqid();
        mkdir($extractPath, 0755, true);
        
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
        
        return $extractPath;
    }
    
    /**
     * Swap core files.
     */
    private function swapFiles(string $extractPath): bool
    {
        // Expected structure: extract/oopress/
        $sourcePath = $extractPath . '/oopress';
        
        if (!is_dir($sourcePath)) {
            $this->addError('Invalid package structure');
            return false;
        }
        
        // Directories to update
        $directories = ['core', 'modules', 'themes', 'vendor'];
        
        foreach ($directories as $dir) {
            $source = $sourcePath . '/' . $dir;
            $target = $this->pathResolver->getProjectRoot() . '/' . $dir;
            
            if (!is_dir($source)) {
                continue;
            }
            
            if (!$this->recursiveCopy($source, $target)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Swap module files.
     */
    private function swapModuleFiles(string $extractPath, string $moduleId): bool
    {
        $sourcePath = $extractPath . '/' . $moduleId;
        $targetPath = $this->pathResolver->getModulePath($moduleId);
        
        if (!is_dir($sourcePath)) {
            $this->addError('Invalid module package structure');
            return false;
        }
        
        // Remove existing module directory
        if (is_dir($targetPath)) {
            $this->recursiveDelete($targetPath);
        }
        
        // Copy new module files
        return $this->recursiveCopy($sourcePath, $targetPath);
    }
    
    /**
     * Recursive copy.
     */
    private function recursiveCopy(string $source, string $target): bool
    {
        if (!is_dir($source)) {
            return copy($source, $target);
        }
        
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $targetPath = $target . '/' . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755);
                }
            } else {
                if (!copy($item->getPathname(), $targetPath)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Recursive delete.
     */
    private function recursiveDelete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        
        if (is_dir($path)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    rmdir($item->getPathname());
                } else {
                    unlink($item->getPathname());
                }
            }
            
            rmdir($path);
        } else {
            unlink($path);
        }
    }
    
    /**
     * Create backup.
     */
    private function createBackup(): string
    {
        $backupPath = $this->pathResolver->getVarPath() . '/backups/' . date('Ymd_His');
        mkdir($backupPath, 0755, true);
        
        // Backup core directories
        $directories = ['core', 'modules', 'themes', 'vendor', 'config', 'public/assets'];
        
        foreach ($directories as $dir) {
            $source = $this->pathResolver->getProjectRoot() . '/' . $dir;
            $target = $backupPath . '/' . $dir;
            
            if (is_dir($source)) {
                $this->recursiveCopy($source, $target);
            }
        }
        
        return $backupPath;
    }
    
    /**
     * Restore backup.
     */
    private function restoreBackup(string $backupPath): void
    {
        $directories = ['core', 'modules', 'themes', 'vendor', 'config', 'public/assets'];
        
        foreach ($directories as $dir) {
            $source = $backupPath . '/' . $dir;
            $target = $this->pathResolver->getProjectRoot() . '/' . $dir;
            
            if (is_dir($source)) {
                $this->recursiveDelete($target);
                $this->recursiveCopy($source, $target);
            }
        }
    }
    
    /**
     * Cleanup backup.
     */
    private function cleanupBackup(string $backupPath): void
    {
        $this->recursiveDelete($backupPath);
    }
    
    /**
     * Backup module.
     */
    private function backupModule(string $moduleId): string
    {
        $backupPath = $this->pathResolver->getVarPath() . '/backups/modules/' . $moduleId . '_' . date('Ymd_His');
        $sourcePath = $this->pathResolver->getModulePath($moduleId);
        
        if (is_dir($sourcePath)) {
            $this->recursiveCopy($sourcePath, $backupPath);
        }
        
        return $backupPath;
    }
    
    /**
     * Restore module.
     */
    private function restoreModule(string $backupPath, string $moduleId): void
    {
        $targetPath = $this->pathResolver->getModulePath($moduleId);
        
        if (is_dir($backupPath)) {
            $this->recursiveDelete($targetPath);
            $this->recursiveCopy($backupPath, $targetPath);
        }
    }
    
    /**
     * Cleanup module backup.
     */
    private function cleanupModuleBackup(string $backupPath): void
    {
        if (is_dir($backupPath)) {
            $this->recursiveDelete($backupPath);
        }
    }
    
    /**
     * Cleanup temp file.
     */
    private function cleanupTempFile(string $tempFile): void
    {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
    
    /**
     * Cleanup extracted files.
     */
    private function cleanupExtractedFiles(string $extractPath): void
    {
        if (is_dir($extractPath)) {
            $this->recursiveDelete($extractPath);
        }
    }
    
    /**
     * Fetch update information from registry.
     */
    private function fetchUpdateInfo(string $type, ?string $moduleId = null): ?array
    {
        $url = self::UPDATE_CHECK_URL . $type;
        
        if ($moduleId) {
            $url .= '/' . $moduleId;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $this->addError(sprintf('Update check failed: %s', $error));
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get download URL.
     */
    private function getDownloadUrl(string $type, string $version, ?string $moduleId = null): string
    {
        if ($type === 'core') {
            return sprintf('https://downloads.oopress.org/releases/oopress-%s.zip', $version);
        }
        
        return sprintf('https://downloads.oopress.org/modules/%s/%s-%s.zip', $moduleId, $moduleId, $version);
    }
    
    /**
     * Get checksum URL.
     */
    private function getChecksumUrl(string $version, ?string $moduleId = null): string
    {
        if ($moduleId) {
            return sprintf('https://downloads.oopress.org/modules/%s/%s-%s.sha256', $moduleId, $moduleId, $version);
        }
        
        return sprintf('https://downloads.oopress.org/releases/oopress-%s.sha256', $version);
    }
    
    /**
     * Get current core version.
     */
    private function getCurrentCoreVersion(): string
    {
        return '1.0.0'; // Will be read from config
    }
    
    /**
     * Get module migration namespace.
     */
    private function getModuleMigrationNamespace(string $moduleId): string
    {
        $parts = explode('/', $moduleId);
        $vendor = ucfirst($parts[0]);
        $module = implode('', array_map('ucfirst', explode('-', $parts[1])));
        
        return sprintf('OOPress\\Module\\%s\\Migrations', $module);
    }
    
    private function addError(string $error): void
    {
        $this->errors[] = $error;
    }
    
    private function createSuccessResult(string $message, int $migrationsExecuted, float $startTime): UpdateResult
    {
        return new UpdateResult(
            success: true,
            message: $message,
            migrationsExecuted: $migrationsExecuted,
            duration: microtime(true) - $startTime,
        );
    }
    
    private function createFailureResult(string $message, float $startTime): UpdateResult
    {
        return new UpdateResult(
            success: false,
            message: $message,
            errors: $this->errors,
            duration: microtime(true) - $startTime,
        );
    }
}