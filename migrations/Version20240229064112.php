<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229064112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE department_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE entity_property_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE entity_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE organization_company_id_seq CASCADE');
        $this->addSql('ALTER TABLE department_managers DROP CONSTRAINT fk_90f7fa2ca76ed395');
        $this->addSql('ALTER TABLE department_managers DROP CONSTRAINT fk_90f7fa2cae80f5df');
        $this->addSql('DROP TABLE department_managers');
        $this->addSql('ALTER TABLE org_company ADD erp_department_code VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE platform_entity_property DROP CONSTRAINT FK_FA128BBF81257D5D');
        $this->addSql('ALTER TABLE platform_entity_property ADD CONSTRAINT FK_FA128BBF81257D5D FOREIGN KEY (entity_id) REFERENCES platform_entity (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messenger_messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER available_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER delivered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE department_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE entity_property_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE entity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE organization_company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE department_managers (user_id INT NOT NULL, department_id INT NOT NULL, PRIMARY KEY(user_id, department_id))');
        $this->addSql('CREATE INDEX idx_90f7fa2cae80f5df ON department_managers (department_id)');
        $this->addSql('CREATE INDEX idx_90f7fa2ca76ed395 ON department_managers (user_id)');
        $this->addSql('ALTER TABLE department_managers ADD CONSTRAINT fk_90f7fa2ca76ed395 FOREIGN KEY (user_id) REFERENCES org_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE department_managers ADD CONSTRAINT fk_90f7fa2cae80f5df FOREIGN KEY (department_id) REFERENCES org_department (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messenger_messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER available_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER delivered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS NULL');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS NULL');
        $this->addSql('ALTER TABLE platform_entity_property DROP CONSTRAINT fk_fa128bbf81257d5d');
        $this->addSql('ALTER TABLE platform_entity_property ADD CONSTRAINT fk_fa128bbf81257d5d FOREIGN KEY (entity_id) REFERENCES platform_entity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE org_company DROP erp_department_code');
    }
}
