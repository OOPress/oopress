<?php

use Medoo\Medoo;

return new class {
    public function run(Medoo $db): void
    {
        $termCount = $db->count('terms');
        
        if ($termCount == 0) {
            // Add sample categories
            $categories = ['Technology', 'News', 'Tutorials', 'Announcements'];
            foreach ($categories as $category) {
                $db->insert('terms', [
                    'name' => $category,
                    'slug' => strtolower($category),
                    'description' => "Posts about {$category}"
                ]);
            }
            echo "  ✓ Added " . count($categories) . " sample categories\n";
            
            // Add sample tags
            $tags = ['php', 'cms', 'tutorial', 'web-development'];
            foreach ($tags as $tag) {
                $db->insert('terms', [
                    'name' => $tag,
                    'slug' => $tag,
                    'description' => "Tag: {$tag}"
                ]);
            }
            echo "  ✓ Added " . count($tags) . " sample tags\n";
        } else {
            echo "  ℹ️  Terms already exist, skipping...\n";
        }
    }
};