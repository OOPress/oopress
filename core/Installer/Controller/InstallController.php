<?php

declare(strict_types=1);

namespace OOPress\Installer\Controller;

use OOPress\Installer\Installer;
use OOPress\Installer\InstallerConfig;
use OOPress\Path\PathResolver;
use OOPress\Template\TemplateManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * InstallController — Web installer wizard.
 * 
 * @internal
 */
class InstallController
{
    private const SESSION_KEY = 'oopress_install';
    private const INSTALL_LOCK = 'installed.lock';
    
    private Session $session;
    
    public function __construct(
        private readonly Installer $installer,
        private readonly PathResolver $pathResolver,
        private readonly TemplateManager $templateManager,
        private readonly array $config = [],
    ) {
        $this->session = new Session();
        $this->session->start();
    }
    
    /**
     * Check if already installed.
     */
    private function isInstalled(): bool
    {
        // Check for install lock file
        $lockFile = $this->pathResolver->getVarPath() . '/' . self::INSTALL_LOCK;
        
        if (file_exists($lockFile)) {
            return true;
        }
        
        // Also check via installer
        return $this->installer->isInstalled();
    }
    
    /**
     * Create install lock file.
     */
    private function createLockFile(): void
    {
        $lockFile = $this->pathResolver->getVarPath() . '/' . self::INSTALL_LOCK;
        $content = sprintf(
            "Installed: %s\nVersion: %s\n",
            date('Y-m-d H:i:s'),
            $this->config['version'] ?? '1.0.0'
        );
        
        file_put_contents($lockFile, $content);
    }
    
    /**
     * GET /install
     * Start installation wizard.
     */
    public function index(Request $request): Response
    {
        if ($this->isInstalled()) {
            return new RedirectResponse('/');
        }
        
        return $this->renderStep('welcome', [
            'title' => 'Welcome to OOPress',
            'php_version' => PHP_VERSION,
            'php_min_version' => '8.2',
            'requirements' => $this->checkRequirements(),
        ]);
    }
    
    /**
     * GET /install/requirements
     * Check system requirements.
     */
    public function requirements(Request $request): Response
    {
        if ($this->isInstalled()) {
            return new RedirectResponse('/');
        }
        
        $requirements = $this->checkRequirements();
        $allPassed = $this->allRequirementsPassed($requirements);
        
        return $this->renderStep('requirements', [
            'title' => 'System Requirements',
            'requirements' => $requirements,
            'all_passed' => $allPassed,
        ]);
    }
    
    /**
     * GET /install/database
     * Database configuration.
     */
    public function database(Request $request): Response
    {
        if ($this->isInstalled()) {
            return new RedirectResponse('/');
        }
        
        $errors = [];
        $saved = $this->session->get(self::SESSION_KEY, []);
        
        if ($request->isMethod('POST')) {
            $config = [
                'db_driver' => $request->request->get('db_driver', 'pdo_mysql'),
                'db_host' => $request->request->get('db_host', 'localhost'),
                'db_port' => (int) $request->request->get('db_port', 3306),
                'db_name' => $request->request->get('db_name', ''),
                'db_user' => $request->request->get('db_user', ''),
                'db_password' => $request->request->get('db_password', ''),
                'db_prefix' => $request->request->get('db_prefix', 'oop_'),
            ];
            
            // Test connection
            $connectionOk = $this->testDatabaseConnection($config);
            
            if ($connectionOk) {
                $this->session->set(self::SESSION_KEY, array_merge($saved, $config));
                return new RedirectResponse('/install/site');
            }
            
            $errors[] = 'Could not connect to database. Please check your credentials.';
        }
        
        return $this->renderStep('database', [
            'title' => 'Database Configuration',
            'saved' => $saved,
            'errors' => $errors,
            'drivers' => [
                'pdo_mysql' => 'MySQL',
                'pdo_pgsql' => 'PostgreSQL',
                'pdo_sqlite' => 'SQLite',
            ],
        ]);
    }
    
    /**
     * GET /install/site
     * Site configuration.
     */
    public function site(Request $request): Response
    {
        if ($this->isInstalled()) {
            return new RedirectResponse('/');
        }
        
        $errors = [];
        $saved = $this->session->get(self::SESSION_KEY, []);
        
        if ($request->isMethod('POST')) {
            $config = [
                'site_name' => $request->request->get('site_name', 'My OOPress Site'),
                'site_url' => $request->request->get('site_url', $this->getBaseUrl()),
                'site_email' => $request->request->get('site_email', ''),
                'language' => $request->request->get('language', 'en'),
                'timezone' => $request->request->get('timezone', 'UTC'),
            ];
            
            $errors = $this->validateSiteConfig($config);
            
            if (empty($errors)) {
                $this->session->set(self::SESSION_KEY, array_merge($saved, $config));
                return new RedirectResponse('/install/admin');
            }
        }
        
        return $this->renderStep('site', [
            'title' => 'Site Configuration',
            'saved' => $saved,
            'errors' => $errors,
            'site_url' => $this->getBaseUrl(),
            'timezones' => $this->getTimezones(),
            'languages' => $this->getLanguages(),
        ]);
    }
    
    /**
     * GET /install/admin
     * Admin user creation.
     */
    public function admin(Request $request): Response
    {
        if ($this->isInstalled()) {
            return new RedirectResponse('/');
        }
        
        $errors = [];
        $saved = $this->session->get(self::SESSION_KEY, []);
        
        if ($request->isMethod('POST')) {
            $config = [
                'admin_username' => $request->request->get('admin_username', 'admin'),
                'admin_email' => $request->request->get('admin_email', ''),
                'admin_password' => $request->request->get('admin_password', ''),
                'admin_password_confirm' => $request->request->get('admin_password_confirm', ''),
            ];
            
            $errors = $this->validateAdminConfig($config);
            
            if (empty($errors)) {
                $this->session->set(self::SESSION_KEY, array_merge($saved, $config));
                return new RedirectResponse('/install/confirm');
            }
        }
        
        return $this->renderStep('admin', [
            'title' => 'Admin User',
            'saved' => $saved,
            'errors' => $errors,
        ]);
    }
    
    /**
     * GET /install/confirm
     * Confirm installation.
     */
    public function confirm(Request $request): Response
    {
        if ($this->isInstalled()) {
            return new RedirectResponse('/');
        }
        
        $config = $this->session->get(self::SESSION_KEY, []);
        
        // Check if all required config is present
        $required = ['db_name', 'db_user', 'site_name', 'admin_username', 'admin_email', 'admin_password'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($config[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            // Missing required config, redirect to appropriate step
            if (in_array('db_name', $missing)) {
                return new RedirectResponse('/install/database');
            }
            if (in_array('site_name', $missing)) {
                return new RedirectResponse('/install/site');
            }
            if (in_array('admin_username', $missing)) {
                return new RedirectResponse('/install/admin');
            }
        }
        
        if ($request->isMethod('POST')) {
            if ($request->request->get('confirm') === 'yes') {
                return $this->performInstallation($request);
            } else {
                // User cancelled, clear session
                $this->session->remove(self::SESSION_KEY);
                return new RedirectResponse('/install');
            }
        }
        
        return $this->renderStep('confirm', [
            'title' => 'Confirm Installation',
            'config' => $config,
        ]);
    }
    
    /**
     * POST /install/complete
     * Perform installation.
     */
    private function performInstallation(Request $request): Response
    {
        $config = $this->session->get(self::SESSION_KEY, []);
        
        // Create installer config
        $installerConfig = new InstallerConfig(
            adminUsername: $config['admin_username'],
            adminEmail: $config['admin_email'],
            adminPassword: $config['admin_password'],
            siteName: $config['site_name'],
            siteUrl: $config['site_url'],
            language: $config['language'] ?? 'en',
            timezone: $config['timezone'] ?? 'UTC',
            dbDriver: $config['db_driver'] ?? 'pdo_mysql',
            dbHost: $config['db_host'] ?? 'localhost',
            dbPort: (int) ($config['db_port'] ?? 3306),
            dbName: $config['db_name'],
            dbUser: $config['db_user'],
            dbPassword: $config['db_password'],
        );
        
        // Run installation
        $result = $this->installer->install($installerConfig);
        
        if ($result->success) {
            $this->createLockFile();
            $this->session->remove(self::SESSION_KEY);
            
            return $this->renderStep('complete', [
                'title' => 'Installation Complete',
                'result' => $result,
                'site_url' => $config['site_url'],
                'admin_url' => $config['site_url'] . '/admin',
            ]);
        }
        
        return $this->renderStep('error', [
            'title' => 'Installation Failed',
            'result' => $result,
            'errors' => $result->errors,
        ]);
    }
    
    /**
     * Check system requirements.
     */
    private function checkRequirements(): array
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version',
                'required' => '8.2',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.2', '>='),
                'critical' => true,
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'required' => true,
                'current' => extension_loaded('pdo'),
                'passed' => extension_loaded('pdo'),
                'critical' => true,
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'required' => true,
                'current' => extension_loaded('pdo_mysql'),
                'passed' => extension_loaded('pdo_mysql'),
                'critical' => true,
            ],
            'json' => [
                'name' => 'JSON Extension',
                'required' => true,
                'current' => extension_loaded('json'),
                'passed' => extension_loaded('json'),
                'critical' => true,
            ],
            'mbstring' => [
                'name' => 'MBString Extension',
                'required' => true,
                'current' => extension_loaded('mbstring'),
                'passed' => extension_loaded('mbstring'),
                'critical' => true,
            ],
            'session' => [
                'name' => 'Session Extension',
                'required' => true,
                'current' => extension_loaded('session'),
                'passed' => extension_loaded('session'),
                'critical' => true,
            ],
            'ctype' => [
                'name' => 'CType Extension',
                'required' => true,
                'current' => extension_loaded('ctype'),
                'passed' => extension_loaded('ctype'),
                'critical' => true,
            ],
            'gd' => [
                'name' => 'GD Extension (for images)',
                'required' => false,
                'current' => extension_loaded('gd'),
                'passed' => extension_loaded('gd'),
                'critical' => false,
            ],
            'zip' => [
                'name' => 'Zip Extension (for updates)',
                'required' => false,
                'current' => extension_loaded('zip'),
                'passed' => extension_loaded('zip'),
                'critical' => false,
            ],
            'curl' => [
                'name' => 'cURL Extension (for updates)',
                'required' => false,
                'current' => extension_loaded('curl'),
                'passed' => extension_loaded('curl'),
                'critical' => false,
            ],
        ];
        
        // Check writable directories
        $writablePaths = [
            $this->pathResolver->getVarPath(),
            $this->pathResolver->getFilesPath(),
            dirname($this->pathResolver->getSettingsFile()),
        ];
        
        foreach ($writablePaths as $path) {
            $name = basename($path) . ' (writable)';
            $requirements[$name] = [
                'name' => $name,
                'required' => true,
                'current' => is_writable($path) ? 'Writable' : 'Not writable',
                'passed' => is_writable($path),
                'critical' => true,
            ];
        }
        
        return $requirements;
    }
    
    /**
     * Check if all critical requirements passed.
     */
    private function allRequirementsPassed(array $requirements): bool
    {
        foreach ($requirements as $req) {
            if ($req['critical'] && !$req['passed']) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Test database connection.
     */
    private function testDatabaseConnection(array $config): bool
    {
        try {
            $params = [
                'driver' => $config['db_driver'],
                'host' => $config['db_host'],
                'port' => $config['db_port'],
                'dbname' => $config['db_name'],
                'user' => $config['db_user'],
                'password' => $config['db_password'],
            ];
            
            $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
            $connection->connect();
            $connection->executeQuery('SELECT 1');
            $connection->close();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Validate site configuration.
     */
    private function validateSiteConfig(array $config): array
    {
        $errors = [];
        
        if (empty($config['site_name'])) {
            $errors['site_name'] = 'Site name is required';
        }
        
        if (empty($config['site_url'])) {
            $errors['site_url'] = 'Site URL is required';
        } elseif (!filter_var($config['site_url'], FILTER_VALIDATE_URL)) {
            $errors['site_url'] = 'Invalid URL format';
        }
        
        if (empty($config['site_email'])) {
            $errors['site_email'] = 'Site email is required';
        } elseif (!filter_var($config['site_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['site_email'] = 'Invalid email format';
        }
        
        return $errors;
    }
    
    /**
     * Validate admin configuration.
     */
    private function validateAdminConfig(array $config): array
    {
        $errors = [];
        
        if (empty($config['admin_username'])) {
            $errors['admin_username'] = 'Username is required';
        } elseif (strlen($config['admin_username']) < 3) {
            $errors['admin_username'] = 'Username must be at least 3 characters';
        }
        
        if (empty($config['admin_email'])) {
            $errors['admin_email'] = 'Email is required';
        } elseif (!filter_var($config['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['admin_email'] = 'Invalid email format';
        }
        
        if (empty($config['admin_password'])) {
            $errors['admin_password'] = 'Password is required';
        } elseif (strlen($config['admin_password']) < 8) {
            $errors['admin_password'] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $config['admin_password'])) {
            $errors['admin_password'] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[a-z]/', $config['admin_password'])) {
            $errors['admin_password'] = 'Password must contain at least one lowercase letter';
        } elseif (!preg_match('/[0-9]/', $config['admin_password'])) {
            $errors['admin_password'] = 'Password must contain at least one number';
        }
        
        if ($config['admin_password'] !== $config['admin_password_confirm']) {
            $errors['admin_password_confirm'] = 'Passwords do not match';
        }
        
        return $errors;
    }
    
    /**
     * Get base URL from request.
     */
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $protocol . '://' . $host;
        
        // Remove /install from path if present
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($script);
        if ($basePath !== '/' && $basePath !== '\\') {
            $base .= $basePath;
        }
        
        return rtrim($base, '/');
    }
    
    /**
     * Get list of timezones.
     */
    private function getTimezones(): array
    {
        $timezones = [];
        $zones = \DateTimeZone::listIdentifiers();
        
        foreach ($zones as $zone) {
            $timezones[$zone] = str_replace('_', ' ', $zone);
        }
        
        return $timezones;
    }
    
    /**
     * Get list of languages.
     */
    private function getLanguages(): array
    {
        return [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ar' => 'العربية',
        ];
    }
    
    /**
     * Render installation step.
     */
    private function renderStep(string $step, array $variables = []): Response
    {
        $template = 'install/' . $step . '.html.twig';
        
        // Add common variables
        $variables['step'] = $step;
        $variables['steps'] = $this->getStepList();
        $variables['current_step_index'] = array_search($step, array_keys($this->getStepList()));
        
        $content = $this->templateManager->render($template, $variables);
        
        return new Response($content);
    }
    
    /**
     * Get installation steps.
     */
    private function getStepList(): array
    {
        return [
            'welcome' => 'Welcome',
            'requirements' => 'Requirements',
            'database' => 'Database',
            'site' => 'Site Configuration',
            'admin' => 'Admin User',
            'confirm' => 'Confirm',
            'complete' => 'Complete',
        ];
    }
}