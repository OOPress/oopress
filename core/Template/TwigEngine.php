<?php

declare(strict_types=1);

namespace OOPress\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use OOPress\Block\BlockManager;
use OOPress\Path\PathResolver;

/**
 * TwigEngine — Twig implementation of the template engine abstraction.
 * 
 * @api
 */
class TwigEngine implements TemplateEngineInterface
{
    private Environment $twig;
    private FilesystemLoader $loader;
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly BlockManager $blockManager,
        private readonly array $options = [],
    ) {
        $this->initialize();
    }
    
    /**
     * Initialize Twig environment.
     */
    private function initialize(): void
    {
        $cachePath = $this->options['cache'] ?? ($this->pathResolver->getVarPath() . '/cache/twig');
        $debug = $this->options['debug'] ?? false;
        
        $this->loader = new FilesystemLoader();
        $this->twig = new Environment($this->loader, [
            'cache' => $cachePath,
            'debug' => $debug,
            'auto_reload' => $debug,
            'strict_variables' => $debug,
        ]);
        
        if ($debug) {
            $this->twig->addExtension(new DebugExtension());
        }
        
        // Add core functions
        $this->registerCoreFunctions();
        
        // Add core globals
        $this->addCoreGlobals();
    }
    
    /**
     * Register core Twig functions.
     */
    private function registerCoreFunctions(): void
    {
        // Render region function
        $this->registerFunction('render_region', function (string $region) {
            return $this->blockManager->renderRegion($region, $this->getCurrentRequest());
        });
        
        // Render block function
        $this->registerFunction('render_block', function (string $blockId) {
            $assignments = $this->blockManager->getBlocksForRegion('__direct__');
            foreach ($assignments as $assignment) {
                if ($assignment->blockId === $blockId) {
                    return $this->blockManager->renderBlock($assignment, $this->getCurrentRequest());
                }
            }
            return null;
        });
        
        // URL generation
        $this->registerFunction('url', function (string $route, array $params = []) {
            return $this->generateUrl($route, $params);
        });
        
        // Asset URL
        $this->registerFunction('asset', function (string $path) {
            return '/assets/' . ltrim($path, '/');
        });
        
        // Translation
        $this->registerFunction('t', function (string $message, array $params = [], ?string $domain = null) {
            return $this->translate($message, $params, $domain);
        });
        
        // Dump function (debug only)
        if ($this->options['debug'] ?? false) {
            $this->registerFunction('dump', function (...$vars) {
                foreach ($vars as $var) {
                    var_dump($var);
                }
            });
        }

        // In registerCoreFunctions()
        $this->registerFunction('assets', function () {
            return $this->assetManager->getAssetTags();
        });

        $this->registerFunction('asset_url', function (string $path) {
            return '/assets/' . ltrim($path, '/');
        });

    }
    
    /**
     * Add core global variables.
     */
    private function addCoreGlobals(): void
    {
        $this->addGlobal('app', [
            'name' => 'OOPress',
            'version' => '1.0.0',
            'environment' => $this->options['environment'] ?? 'prod',
            'debug' => $this->options['debug'] ?? false,
        ]);
        
        $this->addGlobal('current_user', $this->getCurrentUser());
        $this->addGlobal('request', $this->getCurrentRequest());
    }
    
    /**
     * Render a template.
     */
    public function render(string $template, array $variables = []): string
    {
        try {
            return $this->twig->render($template, $variables);
        } catch (\Twig\Error\LoaderError $e) {
            // Template not found in default paths, try with namespace
            if (str_contains($template, ':')) {
                return $this->twig->render($template, $variables);
            }
            throw $e;
        }


        // In addCoreGlobals()
        $this->addGlobal('self_host_assets', $this->config['self_host_assets'] ?? true);

    }
    
    /**
     * Check if a template exists.
     */
    public function exists(string $template): bool
    {
        return $this->twig->getLoader()->exists($template);
    }
    
    /**
     * Add a template namespace/path.
     */
    public function addNamespace(string $namespace, string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        
        // Handle module namespace format: "module:users"
        if (str_contains($namespace, ':')) {
            [$type, $name] = explode(':', $namespace, 2);
            $namespace = $type . '_' . $name;
        }
        
        $this->loader->addPath($path, $namespace);
    }
    
    /**
     * Add a global variable.
     */
    public function addGlobal(string $name, mixed $value): void
    {
        $this->twig->addGlobal($name, $value);
    }
    
    /**
     * Register a function.
     */
    public function registerFunction(string $name, callable $callback): void
    {
        $function = new \Twig\TwigFunction($name, $callback);
        $this->twig->addFunction($function);
    }
    
    /**
     * Get the underlying Twig environment.
     */
    public function getEngine(): mixed
    {
        return $this->twig;
    }
    
    /**
     * Get current request (placeholder - will be injected).
     */
    private function getCurrentRequest(): mixed
    {
        // This will be replaced with proper request injection
        return null;
    }
    
    /**
     * Get current user (placeholder).
     */
    private function getCurrentUser(): array
    {
        return ['id' => 0, 'name' => 'Anonymous', 'roles' => ['anonymous']];
    }
    
    /**
     * Generate URL (placeholder).
     */
    private function generateUrl(string $route, array $params = []): string
    {
        return '/' . $route;
    }
    
    /**
     * Translate message (placeholder).
     */
    private function translate(string $message, array $params = [], ?string $domain = null): string
    {
        return $message;
    }
}