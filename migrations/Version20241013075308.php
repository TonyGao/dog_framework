<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241013075308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE admin_menu_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE org_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE org_corporation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE platform_optionvalue_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE platform_options_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE platform_entity_property_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE platform_entity_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE platform_entity_property_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE org_company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE org_department_id_seq CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE admin_menu_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE org_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE org_corporation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE platform_optionvalue_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE platform_options_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE platform_entity_property_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE platform_entity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE platform_entity_property_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE org_company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE org_department_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }
}
