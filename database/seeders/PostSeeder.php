<?php

use Medoo\Medoo;

return new class {
    public function run(Medoo $db): void
    {
        $postCount = $db->count('posts');
        
        if ($postCount == 0) {
            $samplePosts = [
                [
                    'title' => 'Welcome to OOPress',
                    'slug' => 'welcome-to-oopress',
                    'content' => '<h1>Welcome to OOPress!</h1><p>This is your first post. OOPress is a lean, modern PHP CMS built with clean OOP architecture.</p><p>Features:</p><ul><li>MVC architecture</li><li>Database migrations</li><li>i18n support</li><li>No framework bloat</li></ul>',
                    'excerpt' => 'Welcome to OOPress - a modern PHP CMS',
                ],
                [
                    'title' => 'Getting Started with OOPress',
                    'slug' => 'getting-started',
                    'content' => '<h2>Getting Started</h2><p>OOPress is easy to use. Here are some tips to get started...</p>',
                    'excerpt' => 'Learn how to use OOPress effectively',
                ],
                [
                    'title' => 'Building Your First Theme',
                    'slug' => 'building-first-theme',
                    'content' => '<h2>Theme Development</h2><p>Creating themes in OOPress is simple...</p>',
                    'excerpt' => 'Create beautiful themes for OOPress',
                ],
            ];
            
            foreach ($samplePosts as $index => $post) {
                $db->insert('posts', array_merge($post, [
                    'status' => 'published',
                    'type' => 'post',
                    'author_id' => 1,
                    'published_at' => date('Y-m-d H:i:s', strtotime("-{$index} days"))
                ]));
            }
            echo "  ✓ Added " . count($samplePosts) . " sample posts\n";
        } else {
            echo "  ℹ️  Posts already exist, skipping...\n";
        }
    }
};