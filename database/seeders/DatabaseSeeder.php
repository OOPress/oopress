<?php

use Medoo\Medoo;

return new class {
    public function run(Medoo $db): void
    {
        echo "\n🌱 Seeding database...\n\n";
        
        // Run seeders in order (respecting foreign keys)
        $seeders = [
            'UserSeeder',
            'TermSeeder', 
            'PostSeeder'
        ];
        
        foreach ($seeders as $seeder) {
            echo "  Running {$seeder}...\n";
            $seederFile = __DIR__ . "/{$seeder}.php";
            if (file_exists($seederFile)) {
                $instance = require $seederFile;
                $instance->run($db);
            } else {
                echo "  ⚠️  Seeder not found: {$seeder}.php\n";
            }
        }
        
        echo "\n✅ Database seeding complete!\n";
    }
};