<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                author_name VARCHAR(100) NOT NULL,
                author_email VARCHAR(255) NOT NULL,
                author_url VARCHAR(500) DEFAULT NULL,
                author_ip VARCHAR(45) NOT NULL,
                content TEXT NOT NULL,
                status ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
                parent_id INT DEFAULT 0,
                likes INT DEFAULT 0,
                reported INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_post (post_id),
                INDEX idx_user (user_id),
                INDEX idx_status (status),
                INDEX idx_parent (parent_id),
                INDEX idx_created (created_at),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            )
        ");
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS comments");
    }
};