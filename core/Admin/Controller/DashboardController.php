<?php

declare(strict_types=1);

namespace OOPress\Admin\Controller;

use OOPress\Admin\Health\ModuleHealthChecker;
use OOPress\Admin\AdminMenu;
use OOPress\Extension\ExtensionLoader;
use OOPress\Migration\MigrationRunner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DashboardController — Admin dashboard pages.
 * 
 * @internal
 */
class DashboardController
{
    public function __construct(
        private readonly AdminMenu $menu,
        private readonly ExtensionLoader $extensionLoader,
        private readonly ModuleHealthChecker $healthChecker,
        private readonly MigrationRunner $migrationRunner,
    ) {}
    
    /**
     * Main dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $modulesCount = count($this->extensionLoader->getModules());
        $themesCount = count($this->extensionLoader->getThemes());
        
        $healthStatus = $this->healthChecker->getModulesByStatus();
        $hasUpdates = !$this->migrationRunner->isUpToDate();
        
        $content = $this->renderTemplate('admin/dashboard.html.twig', [
            'modules_count' => $modulesCount,
            'themes_count' => $themesCount,
            'modules_healthy' => count($healthStatus['healthy']),
            'modules_warning' => count($healthStatus['warning']),
            'modules_error' => count($healthStatus['error']),
            'modules_security' => count($healthStatus['error']), // Will separate security
            'modules_unverified' => count($healthStatus['unverified']),
            'has_updates' => $hasUpdates,
            'system_info' => $this->getSystemInfo(),
        ]);
        
        return new Response($content);
    }
    
    /**
     * Module health report.
     */
    public function moduleHealth(Request $request): Response
    {
        $healthStatus = $this->healthChecker->getModulesByStatus();
        
        $content = $this->renderTemplate('admin/modules_health.html.twig', [
            'modules' => [
                'healthy' => $healthStatus['healthy'],
                'warning' => $healthStatus['warning'],
                'error' => $healthStatus['error'],
                'unverified' => $healthStatus['unverified'],
            ],
        ]);
        
        return new Response($content);
    }
    
    /**
     * Status report.
     */
    public function statusReport(Request $request): Response
    {
        $content = $this->renderTemplate('admin/status_report.html.twig', [
            'system_info' => $this->getSystemInfo(),
            'php_info' => $this->getPhpInfo(),
            'database_info' => $this->getDatabaseInfo(),
            'directory_permissions' => $this->checkDirectoryPermissions(),
        ]);
        
        return new Response($content);
    }
    
    /**
     * Get system information.
     */
    private function getSystemInfo(): array
    {
        return [
            'oopress_version' => '1.0.0-dev',
            'php_version' => PHP_VERSION,
            'environment' => $_ENV['APP_ENV'] ?? 'prod',
            'debug_mode' => $_ENV['APP_DEBUG'] ?? false,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'operating_system' => PHP_OS,
        ];
    }
    
    /**
     * Get PHP information.
     */
    private function getPhpInfo(): array
    {
        return [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'extensions' => get_loaded_extensions(),
        ];
    }
    
    /**
     * Get database information.
     */
    private function getDatabaseInfo(): array
    {
        // Placeholder - will be implemented when we have DB connection
        return [
            'driver' => 'MySQL',
            'version' => '8.0',
            'database' => 'oopress',
        ];
    }
    
    /**
     * Check directory permissions.
     */
    private function checkDirectoryPermissions(): array
    {
        // Placeholder - will check actual directories
        return [
            'var/' => true,
            'files/' => true,
            'settings.php' => true,
        ];
    }
    
    /**
     * Render a template.
     */
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use Twig when integrated
        return '<h1>Admin Panel</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}