<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016140507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_entity_organization_hello_world (id UUID NOT NULL, owner_corporation_id UUID DEFAULT NULL, owner_company_id UUID DEFAULT NULL, zhi_ji_ming_cheng VARCHAR(255) NOT NULL, order_num INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_526FC0071E22B72C ON app_entity_organization_hello_world (zhi_ji_ming_cheng)');
        $this->addSql('CREATE INDEX IDX_526FC007F617CBEC ON app_entity_organization_hello_world (owner_corporation_id)');
        $this->addSql('CREATE INDEX IDX_526FC007C5F18393 ON app_entity_organization_hello_world (owner_company_id)');
        $this->addSql('COMMENT ON COLUMN app_entity_organization_hello_world.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN app_entity_organization_hello_world.owner_corporation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN app_entity_organization_hello_world.owner_company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE app_entity_organization_hello_world ADD CONSTRAINT FK_526FC007F617CBEC FOREIGN KEY (owner_corporation_id) REFERENCES org_corporation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE app_entity_organization_hello_world ADD CONSTRAINT FK_526FC007C5F18393 FOREIGN KEY (owner_company_id) REFERENCES org_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE org_department ADD t_est VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4841AE4E4478383D ON org_department (t_est)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE app_entity_organization_hello_world DROP CONSTRAINT FK_526FC007F617CBEC');
        $this->addSql('ALTER TABLE app_entity_organization_hello_world DROP CONSTRAINT FK_526FC007C5F18393');
        $this->addSql('DROP TABLE app_entity_organization_hello_world');
        $this->addSql('DROP INDEX UNIQ_4841AE4E4478383D');
        $this->addSql('ALTER TABLE org_department DROP t_est');
    }
}
