<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS media (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                path VARCHAR(500) NOT NULL,
                url VARCHAR(500) NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                size INT NOT NULL,
                width INT DEFAULT NULL,
                height INT DEFAULT NULL,
                alt_text VARCHAR(255) DEFAULT NULL,
                title VARCHAR(255) DEFAULT NULL,
                caption TEXT DEFAULT NULL,
                author_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_author (author_id),
                INDEX idx_mime (mime_type),
                INDEX idx_created (created_at)
            )
        ");
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS media");
    }
};