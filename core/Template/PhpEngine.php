<?php

declare(strict_types=1);

namespace OOPress\Template;

use OOPress\Block\BlockManager;

/**
 * PhpEngine — Plain PHP template engine (fallback).
 * 
 * @api
 */
class PhpEngine implements TemplateEngineInterface
{
    /**
     * @var array<string, string> Namespace paths
     */
    private array $namespaces = [];
    
    /**
     * @var array<string, mixed> Global variables
     */
    private array $globals = [];
    
    /**
     * @var array<string, callable> Registered functions
     */
    private array $functions = [];
    
    public function __construct(
        private readonly BlockManager $blockManager,
        private readonly string $themePath,
    ) {
        $this->registerCoreFunctions();
    }
    
    /**
     * Register core PHP functions.
     */
    private function registerCoreFunctions(): void
    {
        $this->registerFunction('render_region', function (string $region) {
            return $this->blockManager->renderRegion($region, $this->getCurrentRequest());
        });
        
        $this->registerFunction('render_block', function (string $blockId) {
            $assignments = $this->blockManager->getBlocksForRegion('__direct__');
            foreach ($assignments as $assignment) {
                if ($assignment->blockId === $blockId) {
                    return $this->blockManager->renderBlock($assignment, $this->getCurrentRequest());
                }
            }
            return null;
        });
        
        $this->registerFunction('t', function (string $message, array $params = []) {
            return $this->translate($message, $params);
        });
        
        $this->registerFunction('url', function (string $route, array $params = []) {
            return $this->generateUrl($route, $params);
        });
    }
    
    /**
     * Render a PHP template.
     */
    public function render(string $template, array $variables = []): string
    {
        $templatePath = $this->resolveTemplatePath($template);
        
        if (!$templatePath) {
            throw new \RuntimeException(sprintf('Template not found: %s', $template));
        }
        
        // Extract variables to local scope
        $vars = array_merge($this->globals, $variables);
        extract($vars);
        
        // Capture output
        ob_start();
        try {
            include $templatePath;
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
    
    /**
     * Check if a template exists.
     */
    public function exists(string $template): bool
    {
        return $this->resolveTemplatePath($template) !== null;
    }
    
    /**
     * Add a template namespace/path.
     */
    public function addNamespace(string $namespace, string $path): void
    {
        $this->namespaces[$namespace] = rtrim($path, '/');
    }
    
    /**
     * Add a global variable.
     */
    public function addGlobal(string $name, mixed $value): void
    {
        $this->globals[$name] = $value;
    }
    
    /**
     * Register a function.
     */
    public function registerFunction(string $name, callable $callback): void
    {
        $this->functions[$name] = $callback;
        
        // Make function available in templates
        $this->globals[$name] = function (...$args) use ($callback) {
            return $callback(...$args);
        };
    }
    
    /**
     * Get the engine (null for PHP engine).
     */
    public function getEngine(): mixed
    {
        return null;
    }
    
    /**
     * Resolve template path from namespace or theme.
     */
    private function resolveTemplatePath(string $template): ?string
    {
        // Check for namespace: "module:users/user_list.html.php"
        if (str_contains($template, ':')) {
            [$namespace, $path] = explode(':', $template, 2);
            
            if (isset($this->namespaces[$namespace])) {
                $fullPath = $this->namespaces[$namespace] . '/' . $path;
                if (file_exists($fullPath)) {
                    return $fullPath;
                }
            }
        }
        
        // Check in theme
        $themePath = $this->themePath . '/' . $template;
        if (file_exists($themePath)) {
            return $themePath;
        }
        
        return null;
    }
    
    private function getCurrentRequest(): mixed
    {
        return null;
    }
    
    private function translate(string $message, array $params = []): string
    {
        return $message;
    }
    
    private function generateUrl(string $route, array $params = []): string
    {
        return '/' . $route;
    }
}