<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306005115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE storage_configs_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE storage_configs (id INT NOT NULL, name VARCHAR(50) NOT NULL, adapter_type VARCHAR(20) NOT NULL, is_default BOOLEAN NOT NULL, config JSON NOT NULL, cdn_domain VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_35554B515E237E06 ON storage_configs (name)');
        $this->addSql('COMMENT ON COLUMN storage_configs.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE storage_files (id VARCHAR(36) NOT NULL, disk VARCHAR(50) NOT NULL, path VARCHAR(1024) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, size BIGINT NOT NULL, hash VARCHAR(64) NOT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, metadata JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_file_hash ON storage_files (hash)');
        $this->addSql('CREATE INDEX idx_file_path ON storage_files (path)');
        $this->addSql('COMMENT ON COLUMN storage_files.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE storage_upload_sessions (id VARCHAR(36) NOT NULL, filename VARCHAR(255) NOT NULL, file_hash VARCHAR(64) NOT NULL, total_chunks INT NOT NULL, uploaded_chunks JSON NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN storage_upload_sessions.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN storage_upload_sessions.expires_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE storage_configs_id_seq CASCADE');
        $this->addSql('DROP TABLE storage_configs');
        $this->addSql('DROP TABLE storage_files');
        $this->addSql('DROP TABLE storage_upload_sessions');
    }
}
