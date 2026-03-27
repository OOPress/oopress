<?php

declare(strict_types=1);

namespace OOPress\Extension;

use OOPress\Path\PathResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * ExtensionLoader — Discovers and loads modules and themes.
 * 
 * This is the entry point for all extension operations. It scans the
 * modules/ and themes/ directories, parses manifests, and provides
 * access to discovered extensions.
 * 
 * @api
 */
class ExtensionLoader
{
    /**
     * @var array<string, ExtensionManifest> Discovered modules keyed by ID
     */
    private array $modules = [];

    /**
     * @var array<string, ExtensionManifest> Discovered themes keyed by ID
     */
    private array $themes = [];

    /**
     * @var array<string> Errors encountered during discovery
     */
    private array $errors = [];

    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly bool $enableAutoDiscovery = true,
    ) {
        if ($enableAutoDiscovery) {
            $this->discoverAll();
        }
    }

    /**
     * Discover all modules and themes.
     */
    public function discoverAll(): void
    {
        $this->discoverModules();
        $this->discoverThemes();
    }

    /**
     * Discover modules by scanning the modules directory.
     */
    public function discoverModules(): void
    {
        $modulesPath = $this->pathResolver->getModulesPath();
        
        if (!is_dir($modulesPath)) {
            $this->errors[] = sprintf('Modules directory not found: %s', $modulesPath);
            return;
        }

        $this->scanDirectory($modulesPath, ExtensionType::Module);
    }

    /**
     * Discover themes by scanning the themes directory.
     */
    public function discoverThemes(): void
    {
        $themesPath = $this->pathResolver->getThemesPath();
        
        if (!is_dir($themesPath)) {
            $this->errors[] = sprintf('Themes directory not found: %s', $themesPath);
            return;
        }

        $this->scanDirectory($themesPath, ExtensionType::Theme);
    }

    /**
     * Get a module by its ID.
     */
    public function getModule(string $id): ?ExtensionManifest
    {
        return $this->modules[$id] ?? null;
    }

    /**
     * Get a theme by its ID.
     */
    public function getTheme(string $id): ?ExtensionManifest
    {
        return $this->themes[$id] ?? null;
    }

    /**
     * Get all discovered modules.
     * 
     * @return array<string, ExtensionManifest>
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Get all discovered themes.
     * 
     * @return array<string, ExtensionManifest>
     */
    public function getThemes(): array
    {
        return $this->themes;
    }

    /**
     * Get all errors encountered during discovery.
     * 
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if an extension exists.
     */
    public function hasExtension(string $id, ExtensionType $type): bool
    {
        return $type === ExtensionType::Module 
            ? isset($this->modules[$id])
            : isset($this->themes[$id]);
    }

    /**
     * Scan a directory for extension manifests.
     */
    private function scanDirectory(string $basePath, ExtensionType $type): void
    {
        $iterator = new \DirectoryIterator($basePath);
        
        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isDir()) {
                continue;
            }

            $extensionPath = $item->getPathname();
            $manifestPath = $extensionPath . '/' . $type->value . '.yaml';
            
            if (!file_exists($manifestPath)) {
                continue;
            }

            try {
                $manifest = $this->loadManifest($manifestPath, $type);
                
                // Store by ID
                if ($type === ExtensionType::Module) {
                    $this->modules[$manifest->id] = $manifest;
                } else {
                    $this->themes[$manifest->id] = $manifest;
                }
            } catch (\Exception $e) {
                $this->errors[] = sprintf(
                    'Failed to load %s from %s: %s',
                    $type->value,
                    $extensionPath,
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Load and parse a manifest file.
     */
    private function loadManifest(string $manifestPath, ExtensionType $type): ExtensionManifest
    {
        if (!file_exists($manifestPath)) {
            throw new \RuntimeException(sprintf('Manifest file not found: %s', $manifestPath));
        }

        $contents = file_get_contents($manifestPath);
        if ($contents === false) {
            throw new \RuntimeException(sprintf('Failed to read manifest: %s', $manifestPath));
        }

        $data = Yaml::parse($contents);
        if (!is_array($data)) {
            throw new \RuntimeException(sprintf('Invalid YAML in manifest: %s', $manifestPath));
        }

        return ExtensionManifest::fromArray($data, $type);
    }
}
