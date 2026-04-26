<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS terms (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                description TEXT,
                taxonomy_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_taxonomy (taxonomy_id)
            )
        ");
        
        $db->query("
            CREATE TABLE IF NOT EXISTS taxonomies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL UNIQUE,
                slug VARCHAR(200) NOT NULL UNIQUE,
                description TEXT,
                hierarchical BOOLEAN DEFAULT FALSE
            )
        ");
        
        $db->query("
            CREATE TABLE IF NOT EXISTS term_relationships (
                object_id INT NOT NULL,
                term_id INT NOT NULL,
                PRIMARY KEY (object_id, term_id)
            )
        ");
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS term_relationships");
        $db->query("DROP TABLE IF EXISTS terms");
        $db->query("DROP TABLE IF EXISTS taxonomies");
    }
};