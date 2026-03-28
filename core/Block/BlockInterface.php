<?php

declare(strict_types=1);

namespace OOPress\Block;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BlockInterface — Contract for all blocks.
 * 
 * @api
 */
interface BlockInterface
{
    /**
     * Get the block machine name.
     */
    public function getId(): string;
    
    /**
     * Get the block human-readable label.
     */
    public function getLabel(): string;
    
    /**
     * Get the block description.
     */
    public function getDescription(): string;
    
    /**
     * Get the module that provides this block.
     */
    public function getModule(): string;
    
    /**
     * Get the block category (for admin organization).
     */
    public function getCategory(): string;
    
    /**
     * Render the block content.
     * 
     * @param Request $request The current request
     * @param array<string, mixed> $settings Block instance settings
     * @return string The rendered content
     */
    public function render(Request $request, array $settings = []): string;
    
    /**
     * Get block configuration form.
     * 
     * @param array<string, mixed> $settings Current settings
     * @return array<string, mixed> Form definition
     */
    public function getConfigForm(array $settings = []): array;
    
    /**
     * Validate block configuration.
     * 
     * @param array<string, mixed> $settings Settings to validate
     * @return array<string, string> Validation errors
     */
    public function validateConfig(array $settings): array;
    
    /**
     * Check if the block is cacheable.
     */
    public function isCacheable(): bool;
    
    /**
     * Get cache tags for this block.
     * 
     * @return array<string>
     */
    public function getCacheTags(): array;
    
    /**
     * Get cache context (user role, language, etc.).
     * 
     * @return array<string>
     */
    public function getCacheContexts(): array;
}
