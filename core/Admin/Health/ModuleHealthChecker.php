<?php

declare(strict_types=1);

namespace OOPress\Admin\Health;

use OOPress\Extension\ExtensionLoader;
use OOPress\Extension\ExtensionManifest;

/**
 * ModuleHealthChecker — Checks module health and displays status.
 * 
 * This implements the two-signal verified system:
 * - author_verified: Self-reported by module author
 * - registry_verified: Set by OOPress registry based on automated testing
 * 
 * @api
 */
class ModuleHealthChecker
{
    /**
     * @var array<string, ModuleHealthInfo>
     */
    private array $healthInfo = [];
    
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
    ) {
        $this->checkAllModules();
    }
    
    /**
     * Check health of all modules.
     */
    private function checkAllModules(): void
    {
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            $this->healthInfo[$moduleId] = $this->checkModule($moduleId, $module);
        }
    }
    
    /**
     * Check health of a single module.
     */
    private function checkModule(string $moduleId, ExtensionManifest $manifest): ModuleHealthInfo
    {
        $health = new ModuleHealthInfo($moduleId, $manifest);
        
        // Check author verification
        $authorVerified = $manifest->getVerifiedDate();
        if ($authorVerified) {
            $health->setAuthorVerified($authorVerified);
            
            // Check if verification is recent (within 1 year)
            $verifiedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $authorVerified);
            if ($verifiedDate) {
                $oneYearAgo = new \DateTimeImmutable('-1 year');
                if ($verifiedDate < $oneYearAgo) {
                    $health->addWarning('Author verification is over 1 year old');
                }
            }
        } else {
            $health->addWarning('Module has not been verified by the author');
        }
        
        // Check PHP version compatibility
        if (!$manifest->isPhpVersionCompatible(PHP_VERSION)) {
            $health->addError(sprintf(
                'Requires PHP %s, current version is %s',
                $manifest->php['minimum'] ?? 'unknown',
                PHP_VERSION
            ));
        }
        
        // Check API compatibility
        $apiConstraint = $manifest->getApiConstraint();
        if (!$this->isApiCompatible($apiConstraint)) {
            $health->addError(sprintf(
                'Requires OOPress API %s, current version is %s',
                $apiConstraint,
                $this->getCurrentApiVersion()
            ));
        }
        
        // Check dependencies
        $this->checkDependencies($moduleId, $manifest, $health);
        
        // Check for security advisories (placeholder - will connect to registry)
        $this->checkSecurityAdvisories($moduleId, $manifest, $health);
        
        return $health;
    }
    
    /**
     * Check module dependencies.
     */
    private function checkDependencies(string $moduleId, ExtensionManifest $manifest, ModuleHealthInfo $health): void
    {
        if (!$manifest->dependencies) {
            return;
        }
        
        $requires = $manifest->dependencies['requires'] ?? [];
        $installedModules = $this->extensionLoader->getModules();
        
        foreach ($requires as $requiredModule => $constraint) {
            if (!isset($installedModules[$requiredModule])) {
                $health->addError(sprintf(
                    'Missing required module: %s',
                    $requiredModule
                ));
                continue;
            }
            
            $requiredVersion = $installedModules[$requiredModule]->version;
            if (!$this->satisfiesConstraint($requiredVersion, $constraint)) {
                $health->addError(sprintf(
                    'Requires %s version %s, but %s is installed',
                    $requiredModule,
                    $constraint,
                    $requiredVersion
                ));
            }
        }
        
        $conflicts = $manifest->dependencies['conflicts'] ?? [];
        foreach ($conflicts as $conflictModule => $constraint) {
            if (isset($installedModules[$conflictModule])) {
                $installedVersion = $installedModules[$conflictModule]->version;
                if ($this->satisfiesConstraint($installedVersion, $constraint)) {
                    $health->addError(sprintf(
                        'Conflicts with %s version %s',
                        $conflictModule,
                        $constraint
                    ));
                }
            }
        }
    }
    
    /**
     * Check for security advisories.
     */
    private function checkSecurityAdvisories(string $moduleId, ExtensionManifest $manifest, ModuleHealthInfo $health): void
    {
        // This will be implemented when registry is built
        // For now, just a placeholder
        $advisoryFile = $this->getAdvisoryFile();
        
        if ($advisoryFile && file_exists($advisoryFile)) {
            $advisories = json_decode(file_get_contents($advisoryFile), true);
            
            foreach ($advisories as $advisory) {
                if ($advisory['module'] === $moduleId) {
                    $affectedVersions = $advisory['affected_versions'] ?? [];
                    if ($this->satisfiesConstraint($manifest->version, $affectedVersions)) {
                        $health->addSecurityAdvisory($advisory);
                    }
                }
            }
        }
    }
    
    /**
     * Get module health information.
     */
    public function getModuleHealth(string $moduleId): ?ModuleHealthInfo
    {
        return $this->healthInfo[$moduleId] ?? null;
    }
    
    /**
     * Get all module health information.
     * 
     * @return array<string, ModuleHealthInfo>
     */
    public function getAllHealthInfo(): array
    {
        return $this->healthInfo;
    }
    
    /**
     * Get modules by health status.
     * 
     * @return array<string, array<ModuleHealthInfo>>
     */
    public function getModulesByStatus(): array
    {
        $grouped = [
            'healthy' => [],
            'warning' => [],
            'error' => [],
            'unverified' => [],
        ];
        
        foreach ($this->healthInfo as $moduleId => $health) {
            if ($health->hasSecurityAdvisories()) {
                $grouped['error'][] = $health;
            } elseif ($health->hasErrors()) {
                $grouped['error'][] = $health;
            } elseif ($health->hasWarnings()) {
                $grouped['warning'][] = $health;
            } elseif (!$health->isAuthorVerified()) {
                $grouped['unverified'][] = $health;
            } else {
                $grouped['healthy'][] = $health;
            }
        }
        
        return $grouped;
    }
    
    /**
     * Check if API constraint is satisfied.
     */
    private function isApiCompatible(string $constraint): bool
    {
        // Simple semver check - will be expanded
        $currentApi = $this->getCurrentApiVersion();
        
        // For now, assume compatibility if constraint starts with ^1.0
        return str_starts_with($constraint, '^1.0');
    }
    
    /**
     * Get current OOPress API version.
     */
    private function getCurrentApiVersion(): string
    {
        return '1.0.0';
    }
    
    /**
     * Check if version satisfies constraint.
     */
    private function satisfiesConstraint(string $version, string $constraint): bool
    {
        // Simple version check - will be expanded with semver library
        if ($constraint === '*') {
            return true;
        }
        
        if (str_starts_with($constraint, '^')) {
            $requiredMajor = (int) substr($constraint, 1);
            $currentMajor = (int) $version;
            return $currentMajor >= $requiredMajor;
        }
        
        if (str_starts_with($constraint, '~')) {
            $requiredVersion = substr($constraint, 1);
            return version_compare($version, $requiredVersion, '>=');
        }
        
        return version_compare($version, $constraint, '>=');
    }
    
    /**
     * Get the advisory file path.
     */
    private function getAdvisoryFile(): ?string
    {
        // This will be a cached file from the registry
        $cacheFile = sys_get_temp_dir() . '/oopress_advisories.json';
        return file_exists($cacheFile) ? $cacheFile : null;
    }
}