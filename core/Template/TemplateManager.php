<?php

declare(strict_types=1);

namespace OOPress\Template;

use OOPress\Extension\ExtensionLoader;
use OOPress\Path\PathResolver;

/**
 * TemplateManager — Manages template engines and theme discovery.
 * 
 * @api
 */
class TemplateManager
{
    private TemplateEngineInterface $engine;
    private ?string $activeTheme = null;
    private array $templatePaths = [];
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly ExtensionLoader $extensionLoader,
        private readonly array $config = [],
    ) {
        $this->initializeEngine();
        $this->discoverTemplatePaths();
    }
    
    /**
     * Initialize the template engine.
     */
    private function initializeEngine(): void
    {
        $engineType = $this->config['engine'] ?? 'twig';
        
        switch ($engineType) {
            case 'twig':
                $this->engine = new TwigEngine(
                    $this->pathResolver,
                    $this->getBlockManager(),
                    $this->config['twig'] ?? []
                );
                break;
            case 'php':
                $themePath = $this->getActiveThemePath();
                $this->engine = new PhpEngine(
                    $this->getBlockManager(),
                    $themePath
                );
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown template engine: %s', $engineType));
        }
    }
    
    /**
     * Discover template paths from modules and themes.
     */
    private function discoverTemplatePaths(): void
    {
        // Add core templates
        $coreTemplates = $this->pathResolver->getCorePath() . '/Templates';
        if (file_exists($coreTemplates)) {
            $this->engine->addNamespace('core', $coreTemplates);
        }
        
        // Add module templates
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            $modulePath = $this->pathResolver->getModulePath($moduleId);
            $templatesPath = $modulePath . '/Templates';
            
            if (file_exists($templatesPath)) {
                $namespace = 'module:' . $moduleId;
                $this->engine->addNamespace($namespace, $templatesPath);
            }
        }
        
        // Add theme templates
        $activeTheme = $this->getActiveTheme();
        if ($activeTheme) {
            $themePath = $this->pathResolver->getThemePath($activeTheme);
            $templatesPath = $themePath . '/Templates';
            
            if (file_exists($templatesPath)) {
                $this->engine->addNamespace('theme', $templatesPath);
                $this->templatePaths[] = $templatesPath;
            }
        }
    }
    
    /**
     * Render a template.
     */
    public function render(string $template, array $variables = []): string
    {
        return $this->engine->render($template, $variables);
    }
    
    /**
     * Check if a template exists.
     */
    public function exists(string $template): bool
    {
        return $this->engine->exists($template);
    }
    
    /**
     * Get the template engine.
     */
    public function getEngine(): TemplateEngineInterface
    {
        return $this->engine;
    }
    
    /**
     * Add a global variable.
     */
    public function addGlobal(string $name, mixed $value): void
    {
        $this->engine->addGlobal($name, $value);
    }
    
    /**
     * Register a function.
     */
    public function registerFunction(string $name, callable $callback): void
    {
        $this->engine->registerFunction($name, $callback);
    }
    
    /**
     * Get active theme.
     */
    private function getActiveTheme(): ?string
    {
        if ($this->activeTheme === null) {
            // Load from configuration
            $this->activeTheme = $this->config['active_theme'] ?? 'default';
        }
        
        return $this->activeTheme;
    }
    
    /**
     * Get active theme path.
     */
    private function getActiveThemePath(): string
    {
        $activeTheme = $this->getActiveTheme();
        return $this->pathResolver->getThemePath($activeTheme);
    }
    
    /**
     * Get block manager (placeholder - will be injected).
     */
    private function getBlockManager()
    {
        // This will be resolved from container
        return null;
    }
}