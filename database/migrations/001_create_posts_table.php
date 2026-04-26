<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT,
                excerpt TEXT,
                featured_image VARCHAR(500),
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                type VARCHAR(50) DEFAULT 'post',
                author_id INT,
                views INT DEFAULT 0,
                published_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                meta_title VARCHAR(255) DEFAULT NULL,
                meta_description TEXT DEFAULT NULL,
                meta_keywords VARCHAR(500) DEFAULT NULL,
                canonical_url VARCHAR(500) DEFAULT NULL,
                og_title VARCHAR(255) DEFAULT NULL,
                og_description TEXT DEFAULT NULL,
                og_image VARCHAR(500) DEFAULT NULL,
                schema_type VARCHAR(50) DEFAULT 'Article',
                content_format ENUM('html', 'markdown', 'tinymce', 'php') DEFAULT 'tinymce',
                INDEX idx_status (status),
                INDEX idx_slug (slug),
                INDEX idx_author (author_id),
                INDEX idx_published_at (published_at),
                INDEX idx_type (type)
            )
        ");
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS posts");
    }
};