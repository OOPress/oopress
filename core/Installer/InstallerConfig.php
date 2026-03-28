<?php

declare(strict_types=1);

namespace OOPress\Installer;

/**
 * InstallerConfig — Configuration value object for installation.
 * 
 * @api
 */
class InstallerConfig
{
    public function __construct(
        // Admin user
        public readonly string $adminUsername,
        public readonly string $adminEmail,
        public readonly string $adminPassword,
        
        // Site settings
        public readonly string $siteName,
        public readonly string $siteUrl,
        public readonly string $language = 'en',
        public readonly string $timezone = 'UTC',
        
        // Database settings
        public readonly string $dbDriver = 'pdo_mysql',
        public readonly string $dbHost = 'localhost',
        public readonly int $dbPort = 3306,
        public readonly string $dbName = 'oopress',
        public readonly string $dbUser = 'root',
        public readonly string $dbPassword = '',
    ) {}
    
    /**
     * Create from array (useful for form submissions).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            adminUsername: $data['admin_username'] ?? 'admin',
            adminEmail: $data['admin_email'] ?? '',
            adminPassword: $data['admin_password'] ?? '',
            siteName: $data['site_name'] ?? 'My OOPress Site',
            siteUrl: $data['site_url'] ?? 'https://example.com',
            language: $data['language'] ?? 'en',
            timezone: $data['timezone'] ?? 'UTC',
            dbDriver: $data['db_driver'] ?? 'pdo_mysql',
            dbHost: $data['db_host'] ?? 'localhost',
            dbPort: (int) ($data['db_port'] ?? 3306),
            dbName: $data['db_name'] ?? 'oopress',
            dbUser: $data['db_user'] ?? 'root',
            dbPassword: $data['db_password'] ?? '',
        );
    }
    
    /**
     * Validate the configuration.
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->adminUsername)) {
            $errors[] = 'Admin username is required';
        }
        
        if (empty($this->adminEmail) || !filter_var($this->adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid admin email is required';
        }
        
        if (strlen($this->adminPassword) < 8) {
            $errors[] = 'Admin password must be at least 8 characters';
        }
        
        if (empty($this->siteName)) {
            $errors[] = 'Site name is required';
        }
        
        if (empty($this->siteUrl) || !filter_var($this->siteUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Valid site URL is required';
        }
        
        if (empty($this->dbName)) {
            $errors[] = 'Database name is required';
        }
        
        return $errors;
    }
}
