<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $db->query("
            ALTER TABLE posts 
            ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL AFTER content,
            ADD COLUMN meta_description TEXT DEFAULT NULL AFTER meta_title,
            ADD COLUMN meta_keywords VARCHAR(500) DEFAULT NULL AFTER meta_description,
            ADD COLUMN canonical_url VARCHAR(500) DEFAULT NULL AFTER meta_keywords,
            ADD COLUMN og_title VARCHAR(255) DEFAULT NULL AFTER canonical_url,
            ADD COLUMN og_description TEXT DEFAULT NULL AFTER og_title,
            ADD COLUMN og_image VARCHAR(500) DEFAULT NULL AFTER og_description,
            ADD COLUMN schema_type VARCHAR(50) DEFAULT 'Article' AFTER og_image
        ");
    }
    
    public function down(Medoo $db): void
    {
        $db->query("
            ALTER TABLE posts 
            DROP COLUMN meta_title,
            DROP COLUMN meta_description,
            DROP COLUMN meta_keywords,
            DROP COLUMN canonical_url,
            DROP COLUMN og_title,
            DROP COLUMN og_description,
            DROP COLUMN og_image,
            DROP COLUMN schema_type
        ");
    }
};