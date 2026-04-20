<?php

declare(strict_types=1);

namespace OOPress\Core;

class Installer
{
    private string $lockFile;
    private array $requirements;
    
    public function __construct()
    {
        $this->lockFile = __DIR__ . '/../../storage/installed.lock';
        $this->requirements = $this->getRequirements();
    }
    
    /**
     * Check if OOPress is already installed
     */
    public function isInstalled(): bool
    {
        return file_exists($this->lockFile);
    }
    
    /**
     * Mark as installed
     */
    public function markInstalled(): void
    {
        file_put_contents($this->lockFile, date('Y-m-d H:i:s'));
    }
    
    /**
     * Get system requirements
     */
    public function getRequirements(): array
    {
        return [
            'php_version' => [
                'name' => 'PHP Version',
                'required' => '8.2',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.2', '>=')
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL',
                'required' => 'Enabled',
                'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('pdo_mysql')
            ],
            'json' => [
                'name' => 'JSON',
                'required' => 'Enabled',
                'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('json')
            ],
            'mbstring' => [
                'name' => 'MBString',
                'required' => 'Enabled',
                'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('mbstring')
            ],
            'openssl' => [
                'name' => 'OpenSSL',
                'required' => 'Enabled',
                'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('openssl')
            ],
            'fileinfo' => [
                'name' => 'Fileinfo',
                'required' => 'Enabled',
                'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('fileinfo')
            ],
            'curl' => [
                'name' => 'cURL',
                'required' => 'Enabled',
                'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('curl')
            ],
            'zip' => [
                'name' => 'Zip',
                'required' => 'Enabled',
                'current' => extension_loaded('zip') ? 'Enabled' : 'Disabled',
                'passed' => extension_loaded('zip')
            ],
            'writable_storage' => [
                'name' => 'Storage Writable',
                'required' => 'Yes',
                'current' => is_writable(__DIR__ . '/../../storage/') ? 'Writable' : 'Not Writable',
                'passed' => is_writable(__DIR__ . '/../../storage/')
            ],
            'writable_cache' => [
                'name' => 'Cache Writable',
                'required' => 'Yes',
                'current' => is_writable(__DIR__ . '/../../storage/cache/') ? 'Writable' : 'Not Writable',
                'passed' => is_writable(__DIR__ . '/../../storage/cache/')
            ]
        ];
    }
    
    /**
     * Check if all requirements are met
     */
    public function checkRequirements(): bool
    {
        foreach ($this->requirements as $req) {
            if (!$req['passed']) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Test database connection
     */
    public function testDatabaseConnection(string $host, string $name, string $user, string $pass): array
    {
        try {
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
            $pdo = new \PDO($dsn, $user, $pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return ['success' => true, 'message' => 'Connection successful'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Create database tables
     */
    public function createTables(\PDO $db): array
    {
        $errors = [];
        
        // Read migration files
        $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.php');
        sort($migrationFiles);
        
        foreach ($migrationFiles as $file) {
            try {
                $migration = require $file;
                if (method_exists($migration, 'up')) {
                    $migration->up($db);
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to run " . basename($file) . ": " . $e->getMessage();
            }
        }
        
        return $errors;
    }
    
    /**
     * Create admin user
     */
    public function createAdminUser(\PDO $db, string $username, string $email, string $password): bool
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, display_name, role, status, created_at) 
            VALUES (?, ?, ?, ?, 'admin', 'active', NOW())
        ");
        
        return $stmt->execute([$username, $email, $hashed, $username]);
    }
    
    /**
     * Insert default settings
     */
    public function insertDefaultSettings(\PDO $db): void
    {
        $settings = [
            ['site_title', 'OOPress', 'text', 'general', 'Site Title', 'The name of your website'],
            ['site_tagline', 'A modern PHP CMS', 'text', 'general', 'Tagline', 'A brief description of your website'],
            ['site_timezone', 'UTC', 'select', 'general', 'Timezone', 'Default timezone for your site'],
            ['date_format', 'F j, Y', 'select', 'general', 'Date Format', 'How dates are displayed'],
            ['time_format', 'g:i a', 'select', 'general', 'Time Format', 'How times are displayed'],
            ['posts_per_page', '10', 'text', 'reading', 'Posts Per Page', 'Number of posts to display per page'],
            ['show_excerpt', '1', 'checkbox', 'reading', 'Show Excerpts', 'Show post excerpts instead of full content'],
            ['excerpt_length', '55', 'text', 'reading', 'Excerpt Length', 'Number of words in excerpts'],
            ['enable_comments', '1', 'checkbox', 'comments', 'Enable Comments', 'Allow comments on posts'],
            ['comment_moderation', '0', 'checkbox', 'comments', 'Moderate Comments', 'Comments must be approved'],
            ['enable_seo', '1', 'checkbox', 'seo', 'Enable SEO', 'Enable SEO features'],
            ['max_upload_size', '5242880', 'text', 'media', 'Max Upload Size', 'Maximum file size in bytes'],
            ['allowed_image_types', 'jpg,jpeg,png,gif,webp', 'text', 'media', 'Allowed Image Types', 'Comma-separated allowed extensions'],
            ['maintenance_mode', '0', 'checkbox', 'advanced', 'Maintenance Mode', 'Put site in maintenance mode'],
            ['active_theme', 'default', 'text', 'advanced', 'Active Theme', 'Currently active theme'],
            ['page_cache_enabled', '0', 'checkbox', 'cache', 'Enable Page Cache', 'Cache entire pages'],
            ['query_cache_enabled', '1', 'checkbox', 'cache', 'Enable Query Cache', 'Cache database queries'],
            ['cache_ttl', '3600', 'text', 'cache', 'Cache TTL', 'Time to live in seconds']
        ];
        
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, setting_label, setting_description) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
    }
    
    /**
     * Create .env file
     */
    public function createEnvFile(array $data): bool
    {
        $content = "# OOPress Environment Configuration\n";
        $content .= "# Created: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "APP_NAME=\"{$data['site_title']}\"\n";
        $content .= "APP_ENV=production\n";
        $content .= "APP_DEBUG=false\n";
        $content .= "APP_TIMEZONE={$data['timezone']}\n\n";
        $content .= "# Database Configuration\n";
        $content .= "DB_TYPE=mysql\n";
        $content .= "DB_HOST={$data['db_host']}\n";
        $content .= "DB_NAME={$data['db_name']}\n";
        $content .= "DB_USER={$data['db_user']}\n";
        $content .= "DB_PASS={$data['db_pass']}\n\n";
        $content .= "# Mail Configuration\n";
        $content .= "MAIL_HOST=smtp.mailtrap.io\n";
        $content .= "MAIL_PORT=2525\n";
        $content .= "MAIL_USERNAME=\n";
        $content .= "MAIL_PASSWORD=\n";
        $content .= "MAIL_ENCRYPTION=tls\n";
        $content .= "MAIL_FROM_ADDRESS=hello@oopress.com\n";
        $content .= "MAIL_FROM_NAME=\"OOPress\"\n";
        
        return file_put_contents(__DIR__ . '/../../.env', $content) !== false;
    }
}