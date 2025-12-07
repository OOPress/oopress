<?php
namespace OOPress\Core;

interface ModuleInterface
{
    /**
     * Register module routes
     */
    public function register(): void;
}
