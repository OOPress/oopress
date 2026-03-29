<?php

declare(strict_types=1);

namespace OOPress\Migration\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OOPress\Migration\AbstractMigration;

/**
 * Create logs table.
 * 
 * @internal
 */
final class Version20260401000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create logging table';
    }
    
    public function up(Schema $schema): void
    {
        $this->log('Creating logs table...');
        
        $table = $schema->createTable('oop_logs');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('level', 'string', ['length' => 20]);
        $table->addColumn('message', 'text');
        $table->addColumn('context', 'text', ['notnull' => false]);
        $table->addColumn('channel', 'string', ['length' => 50]);
        $table->addColumn('request_uri', 'string', ['length' => 500, 'notnull' => false]);
        $table->addColumn('request_method', 'string', ['length' => 10, 'notnull' => false]);
        $table->addColumn('ip', 'string', ['length' => 45, 'notnull' => false]);
        $table->addColumn('user_agent', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('pid', 'integer');
        $table->addColumn('memory', 'integer');
        $table->addColumn('created_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['level'], 'idx_level');
        $table->addIndex(['channel'], 'idx_channel');
        $table->addIndex(['created_at'], 'idx_created_at');
        $table->addIndex(['ip'], 'idx_ip');
        
        $this->log('Logs table created');
    }
    
    public function down(Schema $schema): void
    {
        $this->log('Dropping logs table...');
        $schema->dropTable('oop_logs');
        $this->log('Logs table dropped');
    }
}