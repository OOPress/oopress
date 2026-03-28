<?php

declare(strict_types=1);

namespace OOPress\Migration\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OOPress\Migration\AbstractMigration;

/**
 * Create media tables.
 * 
 * @internal
 */
final class Version20260330000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create media management tables';
    }
    
    public function up(Schema $schema): void
    {
        $this->log('Creating media table...');
        
        $table = $schema->createTable('oop_media');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('filename', 'string', ['length' => 255]);
        $table->addColumn('original_name', 'string', ['length' => 255]);
        $table->addColumn('path', 'string', ['length' => 500]);
        $table->addColumn('destination', 'string', ['length' => 50, 'default' => 'public']);
        $table->addColumn('mime_type', 'string', ['length' => 100]);
        $table->addColumn('size', 'integer');
        $table->addColumn('extension', 'string', ['length' => 10]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('metadata', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'idx_media_user');
        $table->addIndex(['mime_type'], 'idx_media_mime');
        $table->addIndex(['created_at'], 'idx_media_created');
        
        $this->log('Media table created');
    }
    
    public function down(Schema $schema): void
    {
        $this->log('Dropping media table...');
        $schema->dropTable('oop_media');
        $this->log('Media table dropped');
    }
}