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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
                taxonomy_id INT NOT NULL,
                PRIMARY KEY (object_id, term_id, taxonomy_id)
            )
        ");
        
        // Insert default taxonomies
        $db->insert('taxonomies', [
            'name' => 'Category',
            'slug' => 'category',
            'description' => 'Post categories',
            'hierarchical' => true
        ]);
        
        $db->insert('taxonomies', [
            'name' => 'Tag',
            'slug' => 'tag',
            'description' => 'Post tags',
            'hierarchical' => false
        ]);
        
        // Insert default category
        $db->insert('terms', [
            'name' => 'Uncategorized',
            'slug' => 'uncategorized',
            'description' => 'Default category'
        ]);
    }
    
    public function down(Medoo $db): void
    {
        $db->query("DROP TABLE IF EXISTS term_relationships");
        $db->query("DROP TABLE IF EXISTS term_taxonomies");
        $db->query("DROP TABLE IF EXISTS terms");
    }
};