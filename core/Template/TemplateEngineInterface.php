<?php

declare(strict_types=1);

namespace OOPress\Template;

/**
 * TemplateEngineInterface — Contract for all template engines.
 * 
 * This abstraction allows OOPress to support multiple templating engines
 * (Twig, Blade, plain PHP, etc.) without changing theme or module code.
 * 
 * @api
 */
interface TemplateEngineInterface
{
    /**
     * Render a template.
     * 
     * @param string $template Template name/path
     * @param array<string, mixed> $variables Variables to pass to template
     * @return string Rendered output
     */
    public function render(string $template, array $variables = []): string;
    
    /**
     * Check if a template exists.
     */
    public function exists(string $template): bool;
    
    /**
     * Add a template namespace/path.
     * 
     * @param string $namespace Namespace (e.g., "module:users")
     * @param string $path Path to template directory
     */
    public function addNamespace(string $namespace, string $path): void;
    
    /**
     * Add a global variable available to all templates.
     */
    public function addGlobal(string $name, mixed $value): void;
    
    /**
     * Register a function that can be called from templates.
     */
    public function registerFunction(string $name, callable $callback): void;
    
    /**
     * Get the underlying engine instance (for advanced usage).
     */
    public function getEngine(): mixed;
}