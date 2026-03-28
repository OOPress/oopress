<?php

declare(strict_types=1);

namespace OOPress\Update;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * UpdateController — Web UI for updates.
 * 
 * @internal
 */
class UpdateController
{
    public function __construct(
        private readonly UpdateManager $updateManager,
    ) {}
    
    /**
     * Update dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $coreUpdates = $this->updateManager->checkUpdates('core');
        $moduleUpdates = $this->updateManager->checkUpdates('module');
        
        $content = $this->renderTemplate('admin/update/dashboard.html.twig', [
            'core_updates' => $coreUpdates,
            'module_updates' => $moduleUpdates,
            'has_updates' => !empty($coreUpdates) || !empty($moduleUpdates),
        ]);
        
        return new Response($content);
    }
    
    /**
     * Update core.
     */
    public function updateCore(Request $request): Response
    {
        $version = $request->get('version');
        $dryRun = $request->get('dry_run', false);
        
        if (!$version) {
            return new JsonResponse(['error' => 'Version required'], 400);
        }
        
        $result = $this->updateManager->updateCore($version, $dryRun);
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => $result->success,
                'message' => $result->message,
                'migrations' => $result->migrationsExecuted,
                'duration' => $result->duration,
                'errors' => $result->errors,
            ]);
        }
        
        $content = $this->renderTemplate('admin/update/result.html.twig', [
            'result' => $result,
            'type' => 'core',
        ]);
        
        return new Response($content);
    }
    
    /**
     * Update module.
     */
    public function updateModule(Request $request): Response
    {
        $moduleId = $request->get('module');
        $version = $request->get('version');
        $dryRun = $request->get('dry_run', false);
        
        if (!$moduleId || !$version) {
            return new JsonResponse(['error' => 'Module ID and version required'], 400);
        }
        
        $result = $this->updateManager->updateModule($moduleId, $version, $dryRun);
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => $result->success,
                'message' => $result->message,
                'migrations' => $result->migrationsExecuted,
                'duration' => $result->duration,
                'errors' => $result->errors,
            ]);
        }
        
        $content = $this->renderTemplate('admin/update/result.html.twig', [
            'result' => $result,
            'type' => 'module',
            'module_id' => $moduleId,
        ]);
        
        return new Response($content);
    }
    
    /**
     * Check for updates (AJAX).
     */
    public function checkUpdatesAjax(Request $request): JsonResponse
    {
        $type = $request->get('type', 'all');
        
        $updates = [];
        
        if ($type === 'core' || $type === 'all') {
            $updates['core'] = $this->updateManager->checkUpdates('core');
        }
        
        if ($type === 'module' || $type === 'all') {
            $updates['modules'] = $this->updateManager->checkUpdates('module');
        }
        
        return new JsonResponse([
            'success' => true,
            'updates' => $updates,
            'has_updates' => !empty($updates['core']) || !empty($updates['modules']),
        ]);
    }
    
    /**
     * Render template.
     */
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use TemplateManager
        return '<h1>Update Manager</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}