<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713032151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE platform_view_entity DROP CONSTRAINT fk_c55320b631518c7');
        $this->addSql('ALTER TABLE platform_view_entity DROP CONSTRAINT fk_c55320b681257d5d');
        $this->addSql('DROP TABLE platform_view_entity');
        $this->addSql('ALTER TABLE platform_entity ADD grid_config JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE platform_view_entity (view_id UUID NOT NULL, entity_id UUID NOT NULL, PRIMARY KEY(view_id, entity_id))');
        $this->addSql('CREATE INDEX idx_c55320b631518c7 ON platform_view_entity (view_id)');
        $this->addSql('CREATE INDEX idx_c55320b681257d5d ON platform_view_entity (entity_id)');
        $this->addSql('COMMENT ON COLUMN platform_view_entity.view_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_view_entity.entity_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE platform_view_entity ADD CONSTRAINT fk_c55320b631518c7 FOREIGN KEY (view_id) REFERENCES platform_view (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_view_entity ADD CONSTRAINT fk_c55320b681257d5d FOREIGN KEY (entity_id) REFERENCES platform_entity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_entity DROP grid_config');
    }
}
