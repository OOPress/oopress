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
        
        // Insert default settings
        $defaultSettings = [
            // General Settings
            ['site_title', 'OOPress', 'text', 'general', 'Site Title', 'The name of your website'],
            ['site_tagline', 'A modern PHP CMS', 'text', 'general', 'Tagline', 'A brief description of your website'],
            ['site_timezone', 'UTC', 'select', 'general', 'Timezone', 'Default timezone for your site', 'UTC|America/New_York|Europe/London|Europe/Berlin|Asia/Tokyo|Australia/Sydney'],
            ['date_format', 'F j, Y', 'select', 'general', 'Date Format', 'How dates are displayed', 'F j, Y|Y-m-d|m/d/Y|d/m/Y'],
            ['time_format', 'g:i a', 'select', 'general', 'Time Format', 'How times are displayed', 'g:i a|H:i|g:i A'],
            
            // Reading Settings
            ['posts_per_page', '10', 'text', 'reading', 'Posts Per Page', 'Number of posts to display per page'],
            ['show_excerpt', '1', 'checkbox', 'reading', 'Show Excerpts', 'Show post excerpts instead of full content on blog page'],
            ['excerpt_length', '55', 'text', 'reading', 'Excerpt Length', 'Number of words in excerpts'],
            
            // Comment Settings
            ['enable_comments', '1', 'checkbox', 'comments', 'Enable Comments', 'Allow comments on posts'],
            ['comment_moderation', '0', 'checkbox', 'comments', 'Moderate Comments', 'Comments must be approved before appearing'],
            ['comment_close_days', '14', 'text', 'comments', 'Close Comments After', 'Days after which comments are closed (0 = never)'],
            
            // SEO Settings
            ['enable_seo', '1', 'checkbox', 'seo', 'Enable SEO', 'Enable SEO features'],
            ['meta_description', '', 'textarea', 'seo', 'Default Meta Description', 'Default description for SEO'],
            ['meta_keywords', '', 'text', 'seo', 'Default Meta Keywords', 'Comma-separated default keywords'],
            
            // Media Settings
            ['max_upload_size', '5242880', 'text', 'media', 'Max Upload Size', 'Maximum file size in bytes (default: 5MB)'],
            ['allowed_image_types', 'jpg,jpeg,png,gif,webp', 'text', 'media', 'Allowed Image Types', 'Comma-separated allowed image extensions'],
            
            // Maintenance
            ['maintenance_mode', '0', 'checkbox', 'advanced', 'Maintenance Mode', 'Put site in maintenance mode'],
            ['maintenance_message', 'Site is under maintenance. Please check back later.', 'textarea', 'advanced', 'Maintenance Message', 'Message shown during maintenance']
        ];
        
        foreach ($defaultSettings as $setting) {
            $db->insert('settings', [
                'setting_key' => $setting[0],
                'setting_value' => $setting[1],
                'setting_type' => $setting[2],
                'setting_group' => $setting[3],
                'setting_label' => $setting[4],
                'setting_description' => $setting[5],
                'setting_options' => $setting[6] ?? null
            ]);
        }
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS settings");
    }
};