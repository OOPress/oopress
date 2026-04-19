<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS pages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT,
                excerpt TEXT,
                featured_image VARCHAR(500),
                status ENUM('draft', 'published') DEFAULT 'draft',
                parent_id INT DEFAULT 0,
                menu_order INT DEFAULT 0,
                show_in_menu TINYINT DEFAULT 1,
                page_template VARCHAR(100) DEFAULT 'default',
                author_id INT NOT NULL,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description TEXT DEFAULT NULL,
                meta_keywords VARCHAR(500) DEFAULT NULL,
                canonical_url VARCHAR(500) DEFAULT NULL,
                og_title VARCHAR(255) DEFAULT NULL,
                og_description TEXT DEFAULT NULL,
                og_image VARCHAR(500) DEFAULT NULL,
                schema_type VARCHAR(50) DEFAULT 'Article',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_status (status),
                INDEX idx_parent (parent_id),
                INDEX idx_menu_order (menu_order),
                INDEX idx_author (author_id)
            )
        ");
        
        // Create sample pages
        $db->insert('pages', [
            'title' => 'About Us',
            'slug' => 'about',
            'content' => '<h1>About Us</h1><p>This is the about page. Edit it in the admin panel.</p>',
            'excerpt' => 'About our website',
            'status' => 'published',
            'show_in_menu' => 1,
            'menu_order' => 1,
            'author_id' => 1
        ]);
        
        $db->insert('pages', [
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => '<h1>Contact Us</h1><p>Contact form goes here.</p>',
            'excerpt' => 'Get in touch',
            'status' => 'published',
            'show_in_menu' => 1,
            'menu_order' => 2,
            'author_id' => 1
        ]);
        
        $db->insert('pages', [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => '<h1>Privacy Policy</h1><p>Your privacy policy content goes here.</p>',
            'excerpt' => 'Privacy policy',
            'status' => 'published',
            'show_in_menu' => 0,
            'menu_order' => 0,
            'author_id' => 1
        ]);
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS pages");
    }
};