<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624105543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE captured_pokemon (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, pokemon_id INT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, type2 VARCHAR(50) DEFAULT NULL, description VARCHAR(5000) NOT NULL, name_en VARCHAR(50) NOT NULL, rarity VARCHAR(30) NOT NULL, poke_id INT NOT NULL, capture_date DATETIME NOT NULL, shiny TINYINT(1) NOT NULL, times_captured INT NOT NULL, INDEX IDX_C885E5D17E3C61F9 (owner_id), INDEX IDX_C885E5D12FE71C3E (pokemon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, friend_a_id INT NOT NULL, friend_b_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', accepted TINYINT(1) NOT NULL, INDEX IDX_7234A45FA1A48FB8 (friend_a_id), INDEX IDX_7234A45FB3112056 (friend_b_id), UNIQUE INDEX unique_friendship (friend_a_id, friend_b_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE generation (id INT AUTO_INCREMENT NOT NULL, gen_number INT NOT NULL, gen_region VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE items (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, price INT NOT NULL, description VARCHAR(255) DEFAULT NULL, stats JSON NOT NULL, image VARCHAR(500) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, author VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE pokemon (id INT AUTO_INCREMENT NOT NULL, relate_to_id INT DEFAULT NULL, gen_id INT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, type2 VARCHAR(50) DEFAULT NULL, description VARCHAR(5000) NOT NULL, name_en VARCHAR(50) NOT NULL, rarity VARCHAR(30) NOT NULL, poke_id INT NOT NULL, INDEX IDX_62DC90F3E8BF6915 (relate_to_id), INDEX IDX_62DC90F3B718FA6E (gen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE trade (id INT AUTO_INCREMENT NOT NULL, user1_id INT NOT NULL, user2_id INT NOT NULL, trade_poke1_id INT DEFAULT NULL, trade_poke2_id INT DEFAULT NULL, status INT NOT NULL, user1_status INT NOT NULL, user2_status INT NOT NULL, INDEX IDX_7E1A436656AE248B (user1_id), INDEX IDX_7E1A4366441B8B65 (user2_id), INDEX IDX_7E1A43662D721C6 (trade_poke1_id), INDEX IDX_7E1A436610628E28 (trade_poke2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudonym VARCHAR(20) NOT NULL, creation_date DATETIME NOT NULL, launchs INT NOT NULL, last_obtained_launch DATETIME NOT NULL, avatar VARCHAR(50) DEFAULT NULL, money INT DEFAULT NULL, launch_count INT DEFAULT NULL, score INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6493654B190 (pseudonym), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE user_items (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_3DC1215A76ED395 (user_id), INDEX IDX_3DC1215126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE captured_pokemon ADD CONSTRAINT FK_C885E5D17E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE captured_pokemon ADD CONSTRAINT FK_C885E5D12FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FA1A48FB8 FOREIGN KEY (friend_a_id) REFERENCES user (id) ON DELETE CASCADE
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FB3112056 FOREIGN KEY (friend_b_id) REFERENCES user (id) ON DELETE CASCADE
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3E8BF6915 FOREIGN KEY (relate_to_id) REFERENCES pokemon (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3B718FA6E FOREIGN KEY (gen_id) REFERENCES generation (id)
        // SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A436656AE248B FOREIGN KEY (user1_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366441B8B65 FOREIGN KEY (user2_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A43662D721C6 FOREIGN KEY (trade_poke1_id) REFERENCES captured_pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A436610628E28 FOREIGN KEY (trade_poke2_id) REFERENCES captured_pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_items ADD CONSTRAINT FK_3DC1215A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE user_items ADD CONSTRAINT FK_3DC1215126F525E FOREIGN KEY (item_id) REFERENCES items (id)
        // SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE captured_pokemon DROP FOREIGN KEY FK_C885E5D17E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE captured_pokemon DROP FOREIGN KEY FK_C885E5D12FE71C3E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FA1A48FB8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FB3112056
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon DROP FOREIGN KEY FK_62DC90F3E8BF6915
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon DROP FOREIGN KEY FK_62DC90F3B718FA6E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A436656AE248B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A4366441B8B65
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A43662D721C6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A436610628E28
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_items DROP FOREIGN KEY FK_3DC1215A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_items DROP FOREIGN KEY FK_3DC1215126F525E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE captured_pokemon
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE friendship
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE generation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE items
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE news
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pokemon
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE trade
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_items
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
