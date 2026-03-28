<?php

declare(strict_types=1);

namespace OOPress\Admin\Controller;

use OOPress\Admin\Health\ModuleHealthChecker;
use OOPress\Extension\ExtensionLoader;
use OOPress\Migration\MigrationRunner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ModuleController — Module management pages.
 * 
 * @internal
 */
class ModuleController
{
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
        private readonly ModuleHealthChecker $healthChecker,
        private readonly MigrationRunner $migrationRunner,
    ) {}
    
    /**
     * List all modules.
     */
    public function list(Request $request): Response
    {
        $modules = [];
        
        foreach ($this->extensionLoader->getModules() as $moduleId => $manifest) {
            $health = $this->healthChecker->getModuleHealth($moduleId);
            
            $modules[$moduleId] = [
                'id' => $moduleId,
                'name' => $manifest->name,
                'version' => $manifest->version,
                'stability' => $manifest->stability,
                'description' => $manifest->description,
                'health' => $health,
                'enabled' => true, // Placeholder - will track enabled state
            ];
        }
        
        $content = $this->renderTemplate('admin/modules/list.html.twig', [
            'modules' => $modules,
            'errors' => $this->extensionLoader->getErrors(),
        ]);
        
        return new Response($content);
    }
    
    /**
     * Module details page.
     */
    public function details(string $moduleId, Request $request): Response
    {
        $manifest = $this->extensionLoader->getModule($moduleId);
        
        if (!$manifest) {
            return new Response('Module not found', Response::HTTP_NOT_FOUND);
        }
        
        $health = $this->healthChecker->getModuleHealth($moduleId);
        
        $content = $this->renderTemplate('admin/modules/details.html.twig', [
            'module' => $manifest,
            'health' => $health,
            'dependencies' => $manifest->dependencies ?? [],
        ]);
        
        return new Response($content);
    }
    
    /**
     * Update modules page.
     */
    public function updates(Request $request): Response
    {
        // Placeholder - will check for updates from registry
        $updates = [];
        
        $content = $this->renderTemplate('admin/modules/updates.html.twig', [
            'updates' => $updates,
            'has_updates' => !empty($updates),
        ]);
        
        return new Response($content);
    }
    
    /**
     * Render a template.
     */
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use Twig when integrated
        return '<h1>Module Management</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}