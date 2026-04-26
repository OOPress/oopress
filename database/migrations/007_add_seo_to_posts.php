<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $columns = $db->query("SHOW COLUMNS FROM posts")->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        if (!in_array('meta_title', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL AFTER content");
        }
        if (!in_array('meta_description', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN meta_description TEXT DEFAULT NULL AFTER meta_title");
        }
        if (!in_array('meta_keywords', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN meta_keywords VARCHAR(500) DEFAULT NULL AFTER meta_description");
        }
        if (!in_array('canonical_url', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN canonical_url VARCHAR(500) DEFAULT NULL AFTER meta_keywords");
        }
        if (!in_array('og_title', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN og_title VARCHAR(255) DEFAULT NULL AFTER canonical_url");
        }
        if (!in_array('og_description', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN og_description TEXT DEFAULT NULL AFTER og_title");
        }
        if (!in_array('og_image', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN og_image VARCHAR(500) DEFAULT NULL AFTER og_description");
        }
        if (!in_array('schema_type', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN schema_type VARCHAR(50) DEFAULT 'Article' AFTER og_image");
        }
    }
    
    public function down(Medoo $db): void
    {
        $db->query("ALTER TABLE posts DROP COLUMN meta_title");
        $db->query("ALTER TABLE posts DROP COLUMN meta_description");
        $db->query("ALTER TABLE posts DROP COLUMN meta_keywords");
        $db->query("ALTER TABLE posts DROP COLUMN canonical_url");
        $db->query("ALTER TABLE posts DROP COLUMN og_title");
        $db->query("ALTER TABLE posts DROP COLUMN og_description");
        $db->query("ALTER TABLE posts DROP COLUMN og_image");
        $db->query("ALTER TABLE posts DROP COLUMN schema_type");
    }
};