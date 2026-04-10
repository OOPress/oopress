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
                INDEX idx_status (status),
                INDEX idx_slug (slug),
                INDEX idx_author (author_id),
                INDEX idx_published_at (published_at)
            )
        ");
        
        // Insert sample post
        $db->insert('posts', [
            'title' => 'Welcome to OOPress',
            'slug' => 'welcome-to-oopress',
            'content' => '<h1>Welcome!</h1><p>This is your first post on OOPress, the lean PHP CMS.</p>',
            'excerpt' => 'Welcome to OOPress - a modern, lean PHP CMS',
            'status' => 'published',
            'type' => 'post',
            'author_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS posts");
    }
};