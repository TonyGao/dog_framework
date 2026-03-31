<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329100103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE email_function_binding_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE email_function_binding (id INT NOT NULL, email_template_id UUID DEFAULT NULL, email_config_id UUID DEFAULT NULL, function_code VARCHAR(100) NOT NULL, function_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3525153E4007B59 ON email_function_binding (function_code)');
        $this->addSql('CREATE INDEX IDX_B3525153131A730F ON email_function_binding (email_template_id)');
        $this->addSql('CREATE INDEX IDX_B35251532BD099A8 ON email_function_binding (email_config_id)');
        $this->addSql('COMMENT ON COLUMN email_function_binding.email_template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email_function_binding.email_config_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE email_function_binding ADD CONSTRAINT FK_B3525153131A730F FOREIGN KEY (email_template_id) REFERENCES sys_email_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_function_binding ADD CONSTRAINT FK_B35251532BD099A8 FOREIGN KEY (email_config_id) REFERENCES sys_email_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE email_function_binding_id_seq CASCADE');
        $this->addSql('ALTER TABLE email_function_binding DROP CONSTRAINT FK_B3525153131A730F');
        $this->addSql('ALTER TABLE email_function_binding DROP CONSTRAINT FK_B35251532BD099A8');
        $this->addSql('DROP TABLE email_function_binding');
    }
}
