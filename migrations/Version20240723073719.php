<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723073719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE org_department ADD e_rpbu_men_bian_ma VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE org_department ADD bu_men_jing_li VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4841AE4E47F4FB73 ON org_department (e_rpbu_men_bian_ma)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4841AE4EFB1D488B ON org_department (bu_men_jing_li)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_4841AE4E47F4FB73');
        $this->addSql('DROP INDEX UNIQ_4841AE4EFB1D488B');
        $this->addSql('ALTER TABLE org_department DROP e_rpbu_men_bian_ma');
        $this->addSql('ALTER TABLE org_department DROP bu_men_jing_li');
    }
}
