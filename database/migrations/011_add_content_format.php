<?php

use Medoo\Medoo;

return new class {
    public function up(Medoo $db): void
    {
        $columns = $db->query("SHOW COLUMNS FROM posts")->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        if (!in_array('content_format', $columnNames)) {
            $db->query("ALTER TABLE posts ADD COLUMN content_format ENUM('html', 'markdown', 'tinymce', 'php') DEFAULT 'tinymce' AFTER content");
        }
    }
    
    public function down(Medoo $db): void
    {
        $db->query("ALTER TABLE posts DROP COLUMN content_format");
    }
};