<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                display_name VARCHAR(255),
                role ENUM('admin', 'editor', 'author', 'subscriber') DEFAULT 'subscriber',
                status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_username (username)
            )
        ");
        
        // Create admin user (password: admin123)
        $db->insert('users', [
            'username' => 'admin',
            'email' => 'admin@oopress.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'display_name' => 'Administrator',
            'role' => 'admin',
            'status' => 'active'
        ]);
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS users");
    }
};