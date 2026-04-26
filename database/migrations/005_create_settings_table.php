<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT,
                setting_type ENUM('text', 'textarea', 'checkbox', 'select', 'image') DEFAULT 'text',
                setting_group VARCHAR(50) DEFAULT 'general',
                setting_label VARCHAR(255),
                setting_description TEXT,
                setting_options TEXT,
                setting_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_key (setting_key),
                INDEX idx_group (setting_group),
                INDEX idx_order (setting_order)
            )
        ");
        // No INSERT statements - settings will be added by seeder
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS settings");
    }
};