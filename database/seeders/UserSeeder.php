<?php

use Medoo\Medoo;

return new class {
    public function run(Medoo $db): void
    {
        $userCount = $db->count('users');
        
        if ($userCount == 0) {
            // Create admin user (password: admin123)
            $db->insert('users', [
                'username' => 'admin',
                'email' => 'admin@oopress.com',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'display_name' => 'Administrator',
                'role' => 'admin',
                'status' => 'active'
            ]);
            echo "  ✓ Created admin user (password: admin123)\n";
            
            // Create editor user
            $db->insert('users', [
                'username' => 'editor',
                'email' => 'editor@oopress.com',
                'password' => password_hash('editor123', PASSWORD_BCRYPT),
                'display_name' => 'Content Editor',
                'role' => 'editor',
                'status' => 'active'
            ]);
            echo "  ✓ Created editor user (password: editor123)\n";
        } else {
            echo "  ℹ️  Users already exist, skipping...\n";
        }
    }
};