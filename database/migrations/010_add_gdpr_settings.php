<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS gdpr_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cookie_consent_enabled TINYINT DEFAULT 1,
                cookie_lifetime INT DEFAULT 365,
                privacy_policy_page_id INT DEFAULT NULL,
                imprint_page_id INT DEFAULT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        // No INSERT - will be added by seeder
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS gdpr_settings");
    }
};