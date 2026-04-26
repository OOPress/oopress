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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_status (status),
                INDEX idx_parent (parent_id),
                INDEX idx_menu_order (menu_order),
                INDEX idx_author (author_id)
            )
        ");
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS pages");
    }
};