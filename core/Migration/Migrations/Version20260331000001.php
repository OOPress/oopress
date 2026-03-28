<?php

declare(strict_types=1);

namespace OOPress\Migration\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OOPress\Migration\AbstractMigration;

/**
 * Create search index table.
 * 
 * @internal
 */
final class Version20260331000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create search index table with FULLTEXT support';
    }
    
    public function up(Schema $schema): void
    {
        $this->log('Creating search index table...');
        
        $table = $schema->createTable('oop_search_index');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('document_id', 'string', ['length' => 255]);
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->addColumn('title', 'string', ['length' => 500]);
        $table->addColumn('content', 'text');
        $table->addColumn('url', 'string', ['length' => 500]);
        $table->addColumn('language', 'string', ['length' => 10, 'notnull' => false]);
        $table->addColumn('fields', 'text', ['notnull' => false]);
        $table->addColumn('access_roles', 'text', ['notnull' => false]);
        $table->addColumn('access_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['document_id', 'type'], 'uniq_document_type');
        $table->addIndex(['type'], 'idx_type');
        $table->addIndex(['language'], 'idx_language');
        
        // Add FULLTEXT index for search (MySQL specific)
        // This will need to be adapted for other databases
        $this->addSql('CREATE FULLTEXT INDEX idx_search_content ON oop_search_index (title, content)');
        
        $this->log('Search index table created');
    }
    
    public function down(Schema $schema): void
    {
        $this->log('Dropping search index table...');
        $schema->dropTable('oop_search_index');
        $this->log('Search index table dropped');
    }
}