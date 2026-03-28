<?php

declare(strict_types=1);

namespace OOPress\Asset;

use OOPress\Path\PathResolver;
use OOPress\Event\HookDispatcher;
use OOPress\Extension\ExtensionLoader;

/**
 * AssetManager — Discovers, processes, and serves assets.
 * 
 * GDPR compliance: All assets are self-hosted by default. CDN is optional.
 * 
 * @api
 */
class AssetManager
{
    private const CACHE_VERSION = 'v1';
    
    /**
     * @var array<AssetDefinition>
     */
    private array $assets = [];
    
    /**
     * @var array<string, string> Compiled asset paths
     */
    private array $compiledAssets = [];
    
    private array $errors = [];
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly ExtensionLoader $extensionLoader,
        private readonly HookDispatcher $hookDispatcher,
        private readonly AssetCompiler $compiler,
        private readonly array $config = [],
    ) {
        $this->discoverAssets();
    }
    
    /**
     * Discover assets from all modules and themes.
     */
    public function discoverAssets(): void
    {
        // Discover module assets
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            $this->discoverModuleAssets($moduleId);
        }
        
        // Discover theme assets
        foreach ($this->extensionLoader->getThemes() as $themeId => $theme) {
            $this->discoverThemeAssets($themeId);
        }
        
        // Dispatch event for modules to register assets programmatically
        $event = new Event\AssetDiscoveryEvent($this);
        $this->hookDispatcher->dispatch($event, 'asset.discover');
    }
    
    /**
     * Discover assets from a module.
     */
    private function discoverModuleAssets(string $moduleId): void
    {
        $modulePath = $this->pathResolver->getModulePath($moduleId);
        $assetFile = $modulePath . '/assets.yaml';
        
        if (file_exists($assetFile)) {
            $this->loadAssetsFromFile($assetFile, $moduleId);
        }
        
        // Check for manual assets directory
        $assetsPath = $modulePath . '/assets';
        if (is_dir($assetsPath)) {
            $this->scanAssetDirectory($assetsPath, $moduleId);
        }
    }
    
    /**
     * Discover assets from a theme.
     */
    private function discoverThemeAssets(string $themeId): void
    {
        $themePath = $this->pathResolver->getThemePath($themeId);
        $assetFile = $themePath . '/assets.yaml';
        
        if (file_exists($assetFile)) {
            $this->loadAssetsFromFile($assetFile, $themeId);
        }
        
        // Check for manual assets directory
        $assetsPath = $themePath . '/assets';
        if (is_dir($assetsPath)) {
            $this->scanAssetDirectory($assetsPath, $themeId);
        }
    }
    
    /**
     * Load assets from YAML file.
     */
    private function loadAssetsFromFile(string $filePath, string $sourceId): void
    {
        $yaml = file_get_contents($filePath);
        $data = \Symfony\Component\Yaml\Yaml::parse($yaml);
        
        if (!is_array($data)) {
            return;
        }
        
        foreach ($data['css'] ?? [] as $id => $css) {
            $this->registerAsset(AssetDefinition::fromArray($id, $css, 'css', $sourceId));
        }
        
        foreach ($data['js'] ?? [] as $id => $js) {
            $this->registerAsset(AssetDefinition::fromArray($id, $js, 'js', $sourceId));
        }
        
        foreach ($data['fonts'] ?? [] as $id => $font) {
            $this->registerAsset(AssetDefinition::fromArray($id, $font, 'font', $sourceId));
        }
    }
    
    /**
     * Scan a directory for asset files.
     */
    private function scanAssetDirectory(string $path, string $sourceId): void
    {
        $iterator = new \DirectoryIterator($path);
        
        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }
            
            $extension = $file->getExtension();
            $id = $sourceId . '/' . $file->getBasename('.' . $extension);
            
            if ($extension === 'css') {
                $this->registerAsset(new AssetDefinition(
                    id: $id,
                    sourceId: $sourceId,
                    type: 'css',
                    path: $file->getPathname(),
                    weight: 0,
                    media: 'all',
                ));
            } elseif ($extension === 'js') {
                $this->registerAsset(new AssetDefinition(
                    id: $id,
                    sourceId: $sourceId,
                    type: 'js',
                    path: $file->getPathname(),
                    weight: 0,
                ));
            }
        }
    }
    
    /**
     * Register an asset.
     */
    public function registerAsset(AssetDefinition $asset): void
    {
        $this->assets[$asset->id] = $asset;
    }
    
    /**
     * Get all assets of a specific type.
     * 
     * @return array<AssetDefinition>
     */
    public function getAssets(string $type): array
    {
        $assets = array_filter(
            $this->assets,
            fn($asset) => $asset->type === $type
        );
        
        // Sort by weight
        usort($assets, fn($a, $b) => $a->weight <=> $b->weight);
        
        return $assets;
    }
    
    /**
     * Get CSS assets.
     */
    public function getCssAssets(): array
    {
        return $this->getAssets('css');
    }
    
    /**
     * Get JavaScript assets.
     */
    public function getJsAssets(): array
    {
        return $this->getAssets('js');
    }
    
    /**
     * Get font assets.
     */
    public function getFontAssets(): array
    {
        return $this->getAssets('font');
    }
    
    /**
     * Compile and write all assets.
     */
    public function compileAssets(bool $force = false): AssetCompileResult
    {
        $result = new AssetCompileResult();
        
        $publicAssetsPath = $this->pathResolver->getPublicRoot() . '/assets';
        
        if (!is_dir($publicAssetsPath)) {
            mkdir($publicAssetsPath, 0755, true);
        }
        
        // Compile CSS
        $cssResult = $this->compiler->compileCss(
            $this->getCssAssets(),
            $publicAssetsPath . '/app.css',
            $force
        );
        $result->addCssResult($cssResult);
        
        // Compile JavaScript
        $jsResult = $this->compiler->compileJs(
            $this->getJsAssets(),
            $publicAssetsPath . '/app.js',
            $force
        );
        $result->addJsResult($jsResult);
        
        // Copy fonts
        foreach ($this->getFontAssets() as $font) {
            $targetPath = $publicAssetsPath . '/fonts/' . basename($font->path);
            $this->compiler->copyFont($font->path, $targetPath, $force);
        }
        
        return $result;
    }
    
    /**
     * Get HTML tags for including assets.
     */
    public function getAssetTags(): string
    {
        $html = '';
        
        // CSS
        foreach ($this->getCssAssets() as $asset) {
            if ($asset->cdn && !$this->config['self_host_assets']) {
                $html .= sprintf(
                    '<link rel="stylesheet" href="%s" media="%s">',
                    $asset->cdn,
                    $asset->media
                );
            } else {
                $url = $this->getAssetUrl($asset);
                $html .= sprintf(
                    '<link rel="stylesheet" href="%s" media="%s">',
                    $url,
                    $asset->media
                );
            }
        }
        
        // JavaScript
        foreach ($this->getJsAssets() as $asset) {
            if ($asset->cdn && !$this->config['self_host_assets']) {
                $html .= sprintf(
                    '<script src="%s" %s></script>',
                    $asset->cdn,
                    $asset->defer ? 'defer' : ''
                );
            } else {
                $url = $this->getAssetUrl($asset);
                $html .= sprintf(
                    '<script src="%s" %s></script>',
                    $url,
                    $asset->defer ? 'defer' : ''
                );
            }
        }
        
        return $html;
    }
    
    /**
     * Get asset URL.
     */
    private function getAssetUrl(AssetDefinition $asset): string
    {
        if ($asset->compiled) {
            return '/assets/' . $asset->compiledPath;
        }
        
        return '/assets/' . $asset->sourceId . '/' . basename($asset->path);
    }
    
    /**
     * Get errors.
     */
    public function getErrors(): array
    {
        return array_merge($this->errors, $this->compiler->getErrors());
    }
}