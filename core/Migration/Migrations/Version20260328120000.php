<?php

declare(strict_types=1);

namespace OOPress\Migration\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OOPress\Migration\AbstractMigration;

/**
 * Example migration: Create the initial users table.
 * 
 * @internal
 */
final class Version20260328120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the users table';
    }
    
    public function up(Schema $schema): void
    {
        $this->log('Creating users table...');
        
        $table = $schema->createTable('oop_users');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('status', 'string', ['length' => 50, 'default' => 'active']);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['username']);
        $table->addUniqueIndex(['email']);
        
        $this->log('Users table created successfully');
    }
    
    public function down(Schema $schema): void
    {
        $this->log('Dropping users table...');
        $schema->dropTable('oop_users');
        $this->log('Users table dropped');
    }
}
