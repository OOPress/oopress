<?php

use Medoo\Medoo;

return new class {
    public function run(Medoo $db): void
    {
        echo "🌱 Seeding database...\n\n";
        
        // Check if already seeded
        $userCount = $db->count('users');
        if ($userCount > 0) {
            echo "Database already seeded. Skipping.\n";
            return;
        }
        
        // Default taxonomies
        echo "Adding taxonomies...\n";
        $db->insert('taxonomies', ['name' => 'Category', 'slug' => 'category', 'description' => 'Post categories', 'hierarchical' => 1]);
        $db->insert('taxonomies', ['name' => 'Tag', 'slug' => 'tag', 'description' => 'Post tags', 'hierarchical' => 0]);
        
        // Default term
        $db->insert('terms', ['name' => 'Uncategorized', 'slug' => 'uncategorized', 'description' => 'Default category', 'taxonomy_id' => 1]);
        
        // Default settings
        echo "Adding settings...\n";
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
            ['cache_ttl', '3600', 'text', 'cache', 'Cache TTL', 'Time to live in seconds'],
            ['cookie_banner_enabled', '1', 'checkbox', 'cookies', 'Enable Cookie Banner', 'Show GDPR cookie consent banner'],
            ['contact_email', '', 'text', 'contact', 'Contact Email', 'Email address where contact form submissions are sent']
        ];
        
        foreach ($settings as $setting) {
            $db->insert('settings', [
                'setting_key' => $setting[0],
                'setting_value' => $setting[1],
                'setting_type' => $setting[2],
                'setting_group' => $setting[3],
                'setting_label' => $setting[4],
                'setting_description' => $setting[5] ?? null
            ]);
        }
        
        // GDPR default
        $db->insert('gdpr_settings', [
            'cookie_consent_enabled' => 1,
            'cookie_lifetime' => 365
        ]);
        
        echo "\n✅ Database seeded successfully!\n";
    }
};