<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260311145426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webauthn_credentials (id VARCHAR(255) NOT NULL, employee_id UUID DEFAULT NULL, public_key_credential_id VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, transports JSON NOT NULL, attestation_type VARCHAR(255) NOT NULL, trust_path JSON NOT NULL, aaguid UUID NOT NULL, credential_public_key TEXT NOT NULL, user_handle VARCHAR(255) NOT NULL, counter INT NOT NULL, other_ui JSON DEFAULT NULL, backup_eligible BOOLEAN DEFAULT NULL, backup_status BOOLEAN DEFAULT NULL, uv_initialized BOOLEAN DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DFEA84908C03F15C ON webauthn_credentials (employee_id)');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.employee_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN webauthn_credentials.aaguid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE webauthn_credentials ADD CONSTRAINT FK_DFEA84908C03F15C FOREIGN KEY (employee_id) REFERENCES org_employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE webauthn_credentials DROP CONSTRAINT FK_DFEA84908C03F15C');
        $this->addSql('DROP TABLE webauthn_credentials');
    }
}
