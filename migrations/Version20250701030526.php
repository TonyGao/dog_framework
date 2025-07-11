<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701030526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE platform_view_entity (view_id UUID NOT NULL, entity_id UUID NOT NULL, PRIMARY KEY(view_id, entity_id))');
        $this->addSql('CREATE INDEX IDX_C55320B631518C7 ON platform_view_entity (view_id)');
        $this->addSql('CREATE INDEX IDX_C55320B681257D5D ON platform_view_entity (entity_id)');
        $this->addSql('COMMENT ON COLUMN platform_view_entity.view_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_view_entity.entity_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE platform_view_field (id UUID NOT NULL, view_id UUID DEFAULT NULL, entity_id UUID DEFAULT NULL, owner_corporation_id UUID DEFAULT NULL, owner_company_id UUID DEFAULT NULL, field_name VARCHAR(80) NOT NULL, field_label VARCHAR(100) NOT NULL, field_type VARCHAR(50) DEFAULT NULL, label_inserted BOOLEAN DEFAULT false NOT NULL, value_inserted BOOLEAN DEFAULT false NOT NULL, label_position TEXT DEFAULT NULL, value_position TEXT DEFAULT NULL, sort_order INT DEFAULT 0 NOT NULL, order_num INT DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_412217E831518C7 ON platform_view_field (view_id)');
        $this->addSql('CREATE INDEX IDX_412217E881257D5D ON platform_view_field (entity_id)');
        $this->addSql('CREATE INDEX IDX_412217E8F617CBEC ON platform_view_field (owner_corporation_id)');
        $this->addSql('CREATE INDEX IDX_412217E8C5F18393 ON platform_view_field (owner_company_id)');
        $this->addSql('CREATE INDEX view_field_idx ON platform_view_field (view_id, entity_id)');
        $this->addSql('COMMENT ON COLUMN platform_view_field.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_view_field.view_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_view_field.entity_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_view_field.owner_corporation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_view_field.owner_company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE platform_view_entity ADD CONSTRAINT FK_C55320B631518C7 FOREIGN KEY (view_id) REFERENCES platform_view (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_view_entity ADD CONSTRAINT FK_C55320B681257D5D FOREIGN KEY (entity_id) REFERENCES platform_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_view_field ADD CONSTRAINT FK_412217E831518C7 FOREIGN KEY (view_id) REFERENCES platform_view (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_view_field ADD CONSTRAINT FK_412217E881257D5D FOREIGN KEY (entity_id) REFERENCES platform_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_view_field ADD CONSTRAINT FK_412217E8F617CBEC FOREIGN KEY (owner_corporation_id) REFERENCES org_corporation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_view_field ADD CONSTRAINT FK_412217E8C5F18393 FOREIGN KEY (owner_company_id) REFERENCES org_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE platform_view_entity DROP CONSTRAINT FK_C55320B631518C7');
        $this->addSql('ALTER TABLE platform_view_entity DROP CONSTRAINT FK_C55320B681257D5D');
        $this->addSql('ALTER TABLE platform_view_field DROP CONSTRAINT FK_412217E831518C7');
        $this->addSql('ALTER TABLE platform_view_field DROP CONSTRAINT FK_412217E881257D5D');
        $this->addSql('ALTER TABLE platform_view_field DROP CONSTRAINT FK_412217E8F617CBEC');
        $this->addSql('ALTER TABLE platform_view_field DROP CONSTRAINT FK_412217E8C5F18393');
        $this->addSql('DROP TABLE platform_view_entity');
        $this->addSql('DROP TABLE platform_view_field');
    }
}
