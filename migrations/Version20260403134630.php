<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403134630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE org_department_managers DROP CONSTRAINT fk_2f697e81a76ed395');
        $this->addSql('CREATE SEQUENCE security_password_policy_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE security_password_strength_rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE security_password_policy (id INT NOT NULL, min_length INT DEFAULT 8 NOT NULL, max_length INT DEFAULT 32 NOT NULL, require_uppercase BOOLEAN DEFAULT false NOT NULL, require_lowercase BOOLEAN DEFAULT false NOT NULL, require_number BOOLEAN DEFAULT false NOT NULL, require_special BOOLEAN DEFAULT false NOT NULL, forbid_username BOOLEAN DEFAULT true NOT NULL, forbid_common_password BOOLEAN DEFAULT true NOT NULL, expire_days INT DEFAULT 90 NOT NULL, history_limit INT DEFAULT 3 NOT NULL, max_retry INT DEFAULT 5 NOT NULL, lock_minutes INT DEFAULT 30 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN security_password_policy.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN security_password_policy.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE security_password_strength_rule (id INT NOT NULL, name VARCHAR(255) NOT NULL, expression JSON NOT NULL, sort_order INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE org_user DROP CONSTRAINT fk_147bc2d1c5f18393');
        $this->addSql('ALTER TABLE org_user DROP CONSTRAINT fk_147bc2d1f617cbec');
        $this->addSql('DROP TABLE sys_user');
        $this->addSql('DROP TABLE org_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE security_password_policy_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE security_password_strength_rule_id_seq CASCADE');
        $this->addSql('CREATE TABLE sys_user (id UUID NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_a52ff2c2f85e0677 ON sys_user (username)');
        $this->addSql('COMMENT ON COLUMN sys_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN sys_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE org_user (id UUID NOT NULL, owner_corporation_id UUID DEFAULT NULL, owner_company_id UUID DEFAULT NULL, username VARCHAR(180) NOT NULL, display_name VARCHAR(180) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified BOOLEAN DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, order_num INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_147bc2d1c5f18393 ON org_user (owner_company_id)');
        $this->addSql('CREATE INDEX idx_147bc2d1f617cbec ON org_user (owner_corporation_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_147bc2d1444f97dd ON org_user (phone)');
        $this->addSql('CREATE UNIQUE INDEX uniq_147bc2d1d5499347 ON org_user (display_name)');
        $this->addSql('CREATE UNIQUE INDEX uniq_147bc2d1e7927c74 ON org_user (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_147bc2d1f85e0677 ON org_user (username)');
        $this->addSql('COMMENT ON COLUMN org_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN org_user.owner_corporation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN org_user.owner_company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE org_user ADD CONSTRAINT fk_147bc2d1c5f18393 FOREIGN KEY (owner_company_id) REFERENCES org_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE org_user ADD CONSTRAINT fk_147bc2d1f617cbec FOREIGN KEY (owner_corporation_id) REFERENCES org_corporation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE security_password_policy');
        $this->addSql('DROP TABLE security_password_strength_rule');
        $this->addSql('ALTER TABLE org_department_managers ADD CONSTRAINT fk_2f697e81a76ed395 FOREIGN KEY (employee_id) REFERENCES org_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
