<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250809070146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE platform_database_connection (id UUID NOT NULL, owner_corporation_id UUID DEFAULT NULL, owner_company_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, driver VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, port VARCHAR(255) NOT NULL, database VARCHAR(255) NOT NULL, charset VARCHAR(50) NOT NULL, username VARCHAR(255) NOT NULL, password_encrypted TEXT NOT NULL, dsn VARCHAR(1024) NOT NULL, order_num INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A09C4346F617CBEC ON platform_database_connection (owner_corporation_id)');
        $this->addSql('CREATE INDEX IDX_A09C4346C5F18393 ON platform_database_connection (owner_company_id)');
        $this->addSql('COMMENT ON COLUMN platform_database_connection.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_database_connection.owner_corporation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_database_connection.owner_company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE platform_datagrid (id UUID NOT NULL, data_source_id UUID NOT NULL, owner_corporation_id UUID DEFAULT NULL, owner_company_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, default_config_data JSON DEFAULT NULL, order_num INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A2B7216A1A935C57 ON platform_datagrid (data_source_id)');
        $this->addSql('CREATE INDEX IDX_A2B7216AF617CBEC ON platform_datagrid (owner_corporation_id)');
        $this->addSql('CREATE INDEX IDX_A2B7216AC5F18393 ON platform_datagrid (owner_company_id)');
        $this->addSql('COMMENT ON COLUMN platform_datagrid.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_datagrid.data_source_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_datagrid.owner_corporation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_datagrid.owner_company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE platform_datasource (id UUID NOT NULL, database_connection_id UUID DEFAULT NULL, owner_corporation_id UUID DEFAULT NULL, owner_company_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, resource VARCHAR(255) DEFAULT NULL, query TEXT DEFAULT NULL, params JSON DEFAULT NULL, order_num INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3F9AAD3959B17A3 ON platform_datasource (database_connection_id)');
        $this->addSql('CREATE INDEX IDX_E3F9AAD3F617CBEC ON platform_datasource (owner_corporation_id)');
        $this->addSql('CREATE INDEX IDX_E3F9AAD3C5F18393 ON platform_datasource (owner_company_id)');
        $this->addSql('COMMENT ON COLUMN platform_datasource.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_datasource.database_connection_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_datasource.owner_corporation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_datasource.owner_company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE platform_database_connection ADD CONSTRAINT FK_A09C4346F617CBEC FOREIGN KEY (owner_corporation_id) REFERENCES org_corporation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_database_connection ADD CONSTRAINT FK_A09C4346C5F18393 FOREIGN KEY (owner_company_id) REFERENCES org_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_datagrid ADD CONSTRAINT FK_A2B7216A1A935C57 FOREIGN KEY (data_source_id) REFERENCES platform_datasource (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_datagrid ADD CONSTRAINT FK_A2B7216AF617CBEC FOREIGN KEY (owner_corporation_id) REFERENCES org_corporation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_datagrid ADD CONSTRAINT FK_A2B7216AC5F18393 FOREIGN KEY (owner_company_id) REFERENCES org_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_datasource ADD CONSTRAINT FK_E3F9AAD3959B17A3 FOREIGN KEY (database_connection_id) REFERENCES platform_database_connection (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_datasource ADD CONSTRAINT FK_E3F9AAD3F617CBEC FOREIGN KEY (owner_corporation_id) REFERENCES org_corporation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_datasource ADD CONSTRAINT FK_E3F9AAD3C5F18393 FOREIGN KEY (owner_company_id) REFERENCES org_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE platform_database_connection DROP CONSTRAINT FK_A09C4346F617CBEC');
        $this->addSql('ALTER TABLE platform_database_connection DROP CONSTRAINT FK_A09C4346C5F18393');
        $this->addSql('ALTER TABLE platform_datagrid DROP CONSTRAINT FK_A2B7216A1A935C57');
        $this->addSql('ALTER TABLE platform_datagrid DROP CONSTRAINT FK_A2B7216AF617CBEC');
        $this->addSql('ALTER TABLE platform_datagrid DROP CONSTRAINT FK_A2B7216AC5F18393');
        $this->addSql('ALTER TABLE platform_datasource DROP CONSTRAINT FK_E3F9AAD3959B17A3');
        $this->addSql('ALTER TABLE platform_datasource DROP CONSTRAINT FK_E3F9AAD3F617CBEC');
        $this->addSql('ALTER TABLE platform_datasource DROP CONSTRAINT FK_E3F9AAD3C5F18393');
        $this->addSql('DROP TABLE platform_database_connection');
        $this->addSql('DROP TABLE platform_datagrid');
        $this->addSql('DROP TABLE platform_datasource');
    }
}
