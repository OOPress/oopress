<?php

declare(strict_types=1);

namespace OOPress\Migration\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OOPress\Migration\AbstractMigration;

/**
 * Create block tables.
 * 
 * @internal
 */
final class Version20260329000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create block assignment tables';
    }
    
    public function up(Schema $schema): void
    {
        $this->log('Creating block_assignments table...');
        
        $table = $schema->createTable('oop_block_assignments');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('block_id', 'string', ['length' => 255]);
        $table->addColumn('region', 'string', ['length' => 255]);
        $table->addColumn('weight', 'integer', ['default' => 0]);
        $table->addColumn('settings', 'text', ['notnull' => false]);
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['region'], 'idx_region');
        $table->addUniqueIndex(['block_id', 'region'], 'uniq_block_region');
        
        $this->log('Block assignments table created');
    }
    
    public function down(Schema $schema): void
    {
        $this->log('Dropping block_assignments table...');
        $schema->dropTable('oop_block_assignments');
        $this->log('Block assignments table dropped');
    }
}
