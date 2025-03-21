<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319124035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE captured_pokemon (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, pokemon_id INT NOT NULL, capture_date DATETIME NOT NULL, shiny TINYINT(1) NOT NULL, INDEX IDX_C885E5D17E3C61F9 (owner_id), INDEX IDX_C885E5D12FE71C3E (pokemon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE generation (id INT AUTO_INCREMENT NOT NULL, gen_number INT NOT NULL, gen_region VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE items (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, price INT NOT NULL, description VARCHAR(255) DEFAULT NULL, stats JSON NOT NULL, image VARCHAR(500) NOT NULL, INDEX IDX_E11EE94D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE items_user (items_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8A7F78CF6BB0AE84 (items_id), INDEX IDX_8A7F78CFA76ED395 (user_id), PRIMARY KEY(items_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pokemon (id INT AUTO_INCREMENT NOT NULL, relate_to_id INT DEFAULT NULL, gen_id INT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, type2 VARCHAR(50) DEFAULT NULL, description VARCHAR(5000) NOT NULL, name_en VARCHAR(50) NOT NULL, rarity VARCHAR(30) NOT NULL, poke_id INT NOT NULL, INDEX IDX_62DC90F3E8BF6915 (relate_to_id), INDEX IDX_62DC90F3B718FA6E (gen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudonym VARCHAR(50) NOT NULL, creation_date DATETIME NOT NULL, launchs INT NOT NULL, last_obtained_launch DATETIME NOT NULL, avatar VARCHAR(50) DEFAULT NULL, money INT DEFAULT NULL, launch_count INT DEFAULT NULL, hyper_ball INT DEFAULT NULL, shiny_ball INT DEFAULT NULL, master_ball INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6493654B190 (pseudonym), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE captured_pokemon ADD CONSTRAINT FK_C885E5D17E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE captured_pokemon ADD CONSTRAINT FK_C885E5D12FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
        $this->addSql('ALTER TABLE items ADD CONSTRAINT FK_E11EE94D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE items_user ADD CONSTRAINT FK_8A7F78CF6BB0AE84 FOREIGN KEY (items_id) REFERENCES items (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE items_user ADD CONSTRAINT FK_8A7F78CFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3E8BF6915 FOREIGN KEY (relate_to_id) REFERENCES pokemon (id)');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3B718FA6E FOREIGN KEY (gen_id) REFERENCES generation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE captured_pokemon DROP FOREIGN KEY FK_C885E5D17E3C61F9');
        $this->addSql('ALTER TABLE captured_pokemon DROP FOREIGN KEY FK_C885E5D12FE71C3E');
        $this->addSql('ALTER TABLE items DROP FOREIGN KEY FK_E11EE94D12469DE2');
        $this->addSql('ALTER TABLE items_user DROP FOREIGN KEY FK_8A7F78CF6BB0AE84');
        $this->addSql('ALTER TABLE items_user DROP FOREIGN KEY FK_8A7F78CFA76ED395');
        $this->addSql('ALTER TABLE pokemon DROP FOREIGN KEY FK_62DC90F3E8BF6915');
        $this->addSql('ALTER TABLE pokemon DROP FOREIGN KEY FK_62DC90F3B718FA6E');
        $this->addSql('DROP TABLE captured_pokemon');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE generation');
        $this->addSql('DROP TABLE items');
        $this->addSql('DROP TABLE items_user');
        $this->addSql('DROP TABLE pokemon');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
