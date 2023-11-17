<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231116135750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contacts (id INT AUTO_INCREMENT NOT NULL, app_user_send_id INT NOT NULL, app_user_receive_id INT NOT NULL, relation_status_id INT DEFAULT NULL, send_date DATE NOT NULL, object VARCHAR(255) NOT NULL, INDEX IDX_3340157389103AF0 (app_user_send_id), INDEX IDX_33401573B1048EA9 (app_user_receive_id), INDEX IDX_33401573AE6A2039 (relation_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domains (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domains_research_centers (domains_id INT NOT NULL, research_centers_id INT NOT NULL, INDEX IDX_FA6022A83700F4DC (domains_id), INDEX IDX_FA6022A8ED536D21 (research_centers_id), PRIMARY KEY(domains_id, research_centers_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE investors (id INT AUTO_INCREMENT NOT NULL, app_user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, sigle VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, INDEX IDX_C988F01B4A3353D8 (app_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE locations (id INT AUTO_INCREMENT NOT NULL, address VARCHAR(255) NOT NULL, postal_code INT NOT NULL, commune VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manages (id INT AUTO_INCREMENT NOT NULL, personnel_id INT NOT NULL, research_center_id INT NOT NULL, grade VARCHAR(255) NOT NULL, INDEX IDX_8D23152F1C109075 (personnel_id), INDEX IDX_8D23152F227E238E (research_center_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personnels (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relation_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE research_centers (id INT AUTO_INCREMENT NOT NULL, located_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, sigle VARCHAR(255) NOT NULL, founding_year INT NOT NULL, is_active TINYINT(1) NOT NULL, website VARCHAR(255) NOT NULL, fiche_msr VARCHAR(255) NOT NULL, INDEX IDX_BEE36B2B340BE968 (located_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE research_centers_research_centers (research_centers_source INT NOT NULL, research_centers_target INT NOT NULL, INDEX IDX_116BBFB29BE98EA9 (research_centers_source), INDEX IDX_116BBFB2820CDE26 (research_centers_target), PRIMARY KEY(research_centers_source, research_centers_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE researchers (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE researchers_domains (researchers_id INT NOT NULL, domains_id INT NOT NULL, INDEX IDX_4BD656BA86DD11CD (researchers_id), INDEX IDX_4BD656BA3700F4DC (domains_id), PRIMARY KEY(researchers_id, domains_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tutelles (id INT AUTO_INCREMENT NOT NULL, investor_id INT NOT NULL, research_center_id INT NOT NULL, uai VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_D3B2CDFE9AE528DA (investor_id), INDEX IDX_D3B2CDFE227E238E (research_center_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, researcher_id INT DEFAULT NULL, location_id INT DEFAULT NULL, user_name VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1483A5E924A232CF (user_name), UNIQUE INDEX UNIQ_1483A5E9C7533BDE (researcher_id), INDEX IDX_1483A5E964D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_research_centers (users_id INT NOT NULL, research_centers_id INT NOT NULL, INDEX IDX_AE82648E67B3B43D (users_id), INDEX IDX_AE82648EED536D21 (research_centers_id), PRIMARY KEY(users_id, research_centers_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contacts ADD CONSTRAINT FK_3340157389103AF0 FOREIGN KEY (app_user_send_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contacts ADD CONSTRAINT FK_33401573B1048EA9 FOREIGN KEY (app_user_receive_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contacts ADD CONSTRAINT FK_33401573AE6A2039 FOREIGN KEY (relation_status_id) REFERENCES relation_status (id)');
        $this->addSql('ALTER TABLE domains_research_centers ADD CONSTRAINT FK_FA6022A83700F4DC FOREIGN KEY (domains_id) REFERENCES domains (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE domains_research_centers ADD CONSTRAINT FK_FA6022A8ED536D21 FOREIGN KEY (research_centers_id) REFERENCES research_centers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE investors ADD CONSTRAINT FK_C988F01B4A3353D8 FOREIGN KEY (app_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE manages ADD CONSTRAINT FK_8D23152F1C109075 FOREIGN KEY (personnel_id) REFERENCES personnels (id)');
        $this->addSql('ALTER TABLE manages ADD CONSTRAINT FK_8D23152F227E238E FOREIGN KEY (research_center_id) REFERENCES research_centers (id)');
        $this->addSql('ALTER TABLE research_centers ADD CONSTRAINT FK_BEE36B2B340BE968 FOREIGN KEY (located_id) REFERENCES locations (id)');
        $this->addSql('ALTER TABLE research_centers_research_centers ADD CONSTRAINT FK_116BBFB29BE98EA9 FOREIGN KEY (research_centers_source) REFERENCES research_centers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE research_centers_research_centers ADD CONSTRAINT FK_116BBFB2820CDE26 FOREIGN KEY (research_centers_target) REFERENCES research_centers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE researchers_domains ADD CONSTRAINT FK_4BD656BA86DD11CD FOREIGN KEY (researchers_id) REFERENCES researchers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE researchers_domains ADD CONSTRAINT FK_4BD656BA3700F4DC FOREIGN KEY (domains_id) REFERENCES domains (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tutelles ADD CONSTRAINT FK_D3B2CDFE9AE528DA FOREIGN KEY (investor_id) REFERENCES investors (id)');
        $this->addSql('ALTER TABLE tutelles ADD CONSTRAINT FK_D3B2CDFE227E238E FOREIGN KEY (research_center_id) REFERENCES research_centers (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9C7533BDE FOREIGN KEY (researcher_id) REFERENCES researchers (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E964D218E FOREIGN KEY (location_id) REFERENCES locations (id)');
        $this->addSql('ALTER TABLE users_research_centers ADD CONSTRAINT FK_AE82648E67B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_research_centers ADD CONSTRAINT FK_AE82648EED536D21 FOREIGN KEY (research_centers_id) REFERENCES research_centers (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contacts DROP FOREIGN KEY FK_3340157389103AF0');
        $this->addSql('ALTER TABLE contacts DROP FOREIGN KEY FK_33401573B1048EA9');
        $this->addSql('ALTER TABLE contacts DROP FOREIGN KEY FK_33401573AE6A2039');
        $this->addSql('ALTER TABLE domains_research_centers DROP FOREIGN KEY FK_FA6022A83700F4DC');
        $this->addSql('ALTER TABLE domains_research_centers DROP FOREIGN KEY FK_FA6022A8ED536D21');
        $this->addSql('ALTER TABLE investors DROP FOREIGN KEY FK_C988F01B4A3353D8');
        $this->addSql('ALTER TABLE manages DROP FOREIGN KEY FK_8D23152F1C109075');
        $this->addSql('ALTER TABLE manages DROP FOREIGN KEY FK_8D23152F227E238E');
        $this->addSql('ALTER TABLE research_centers DROP FOREIGN KEY FK_BEE36B2B340BE968');
        $this->addSql('ALTER TABLE research_centers_research_centers DROP FOREIGN KEY FK_116BBFB29BE98EA9');
        $this->addSql('ALTER TABLE research_centers_research_centers DROP FOREIGN KEY FK_116BBFB2820CDE26');
        $this->addSql('ALTER TABLE researchers_domains DROP FOREIGN KEY FK_4BD656BA86DD11CD');
        $this->addSql('ALTER TABLE researchers_domains DROP FOREIGN KEY FK_4BD656BA3700F4DC');
        $this->addSql('ALTER TABLE tutelles DROP FOREIGN KEY FK_D3B2CDFE9AE528DA');
        $this->addSql('ALTER TABLE tutelles DROP FOREIGN KEY FK_D3B2CDFE227E238E');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9C7533BDE');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E964D218E');
        $this->addSql('ALTER TABLE users_research_centers DROP FOREIGN KEY FK_AE82648E67B3B43D');
        $this->addSql('ALTER TABLE users_research_centers DROP FOREIGN KEY FK_AE82648EED536D21');
        $this->addSql('DROP TABLE contacts');
        $this->addSql('DROP TABLE domains');
        $this->addSql('DROP TABLE domains_research_centers');
        $this->addSql('DROP TABLE investors');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE manages');
        $this->addSql('DROP TABLE personnels');
        $this->addSql('DROP TABLE relation_status');
        $this->addSql('DROP TABLE research_centers');
        $this->addSql('DROP TABLE research_centers_research_centers');
        $this->addSql('DROP TABLE researchers');
        $this->addSql('DROP TABLE researchers_domains');
        $this->addSql('DROP TABLE tutelles');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE users_research_centers');
    }
}
