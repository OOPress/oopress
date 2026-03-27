<?php

declare(strict_types=1);

namespace OOPress\Path;

/**
 * PathResolver — The single source of truth for all filesystem paths.
 * 
 * In v1.x, this is simple — paths are static and site-unaware.
 * In v2.x, this becomes site-aware for multisite support.
 * 
 * @api — This is a public contract. The method signatures are guaranteed stable.
 */
class PathResolver
{
    /**
     * The project root — where composer.json lives, above public/.
     */
    private readonly string $projectRoot;

    /**
     * @param string|null $projectRoot Optional project root path.
     *                                 If null, auto-detects by looking for composer.json.
     * @throws \RuntimeException if project root cannot be determined
     */
    public function __construct(?string $projectRoot = null)
    {
        if ($projectRoot !== null) {
            $this->projectRoot = rtrim($projectRoot, '/');
            return;
        }

        // Auto-detect: start from current directory and walk up until we find composer.json
        $this->projectRoot = $this->detectProjectRoot();
    }

    /**
     * Get the project root directory.
     * 
     * @return string Absolute path to project root (no trailing slash)
     */
    public function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    /**
     * Get the public web root directory.
     * 
     * @return string Absolute path to public/ (no trailing slash)
     */
    public function getPublicRoot(): string
    {
        return $this->projectRoot . '/public';
    }

    /**
     * Get the core framework directory.
     * 
     * @return string Absolute path to core/ (no trailing slash)
     */
    public function getCorePath(): string
    {
        return $this->projectRoot . '/core';
    }

    /**
     * Get the modules directory.
     * 
     * @return string Absolute path to modules/ (no trailing slash)
     */
    public function getModulesPath(): string
    {
        return $this->projectRoot . '/modules';
    }

    /**
     * Get the themes directory.
     * 
     * @return string Absolute path to themes/ (no trailing slash)
     */
    public function getThemesPath(): string
    {
        return $this->projectRoot . '/themes';
    }

    /**
     * Get the configuration directory.
     * 
     * @return string Absolute path to config/ (no trailing slash)
     */
    public function getConfigPath(): string
    {
        return $this->projectRoot . '/config';
    }

    /**
     * Get the runtime data directory (cache, logs, sessions).
     * 
     * @return string Absolute path to var/ (no trailing slash)
     */
    public function getVarPath(): string
    {
        return $this->projectRoot . '/var';
    }

    /**
     * Get the user uploads directory.
     * 
     * @return string Absolute path to files/ (no trailing slash)
     */
    public function getFilesPath(): string
    {
        return $this->projectRoot . '/files';
    }

    /**
     * Get the vendor directory.
     * 
     * @return string Absolute path to vendor/ (no trailing slash)
     */
    public function getVendorPath(): string
    {
        return $this->projectRoot . '/vendor';
    }

    /**
     * Get a module's directory.
     * 
     * @param string $moduleId Module ID in format "vendor/name"
     * @return string Absolute path to the module directory
     */
    public function getModulePath(string $moduleId): string
    {
        // Module IDs use namespaced format, which translates directly to filesystem
        // e.g., "oopress/users" -> modules/oopress/users
        return $this->getModulesPath() . '/' . $moduleId;
    }

    /**
     * Get a theme's directory.
     * 
     * @param string $themeId Theme identifier (e.g., "default", "admin")
     * @return string Absolute path to the theme directory
     */
    public function getThemePath(string $themeId): string
    {
        return $this->getThemesPath() . '/' . $themeId;
    }

    /**
     * Get the path to the settings file.
     * 
     * @return string Absolute path to settings.php
     */
    public function getSettingsFile(): string
    {
        return $this->projectRoot . '/settings.php';
    }

    /**
     * Detect the project root by looking for composer.json.
     * 
     * @return string Absolute path to project root
     * @throws \RuntimeException if composer.json not found
     */
    private function detectProjectRoot(): string
    {
        $dir = __DIR__;
        
        // Walk up until we find composer.json or hit filesystem root
        while ($dir !== '/' && $dir !== '') {
            if (file_exists($dir . '/composer.json')) {
                return $dir;
            }
            $dir = dirname($dir);
        }
        
        throw new \RuntimeException(
            'Could not detect project root. No composer.json found in any parent directory.'
        );
    }
}
