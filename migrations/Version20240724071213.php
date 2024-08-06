<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724071213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE org_department ADD bu_men_fu_zong VARCHAR(300) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4841AE4E877E547E ON org_department (bu_men_fu_zong)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_4841AE4E877E547E');
        $this->addSql('ALTER TABLE org_department DROP bu_men_fu_zong');
    }
}
