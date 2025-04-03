<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403102047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_items (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, item_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_3DC1215A76ED395 (user_id), INDEX IDX_3DC1215126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_items ADD CONSTRAINT FK_3DC1215A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_items ADD CONSTRAINT FK_3DC1215126F525E FOREIGN KEY (item_id) REFERENCES items (id)');
        $this->addSql('ALTER TABLE items_user DROP FOREIGN KEY FK_8A7F78CF6BB0AE84');
        $this->addSql('ALTER TABLE items_user DROP FOREIGN KEY FK_8A7F78CFA76ED395');
        $this->addSql('DROP TABLE items_user');
        $this->addSql('ALTER TABLE captured_pokemon ADD times_captured INT NOT NULL');
        $this->addSql('ALTER TABLE items DROP FOREIGN KEY FK_E11EE94D12469DE2');
        $this->addSql('DROP INDEX IDX_E11EE94D12469DE2 ON items');
        $this->addSql('ALTER TABLE items ADD active TINYINT(1) NOT NULL, DROP category_id');
        $this->addSql('ALTER TABLE user DROP hyper_ball, DROP shiny_ball, DROP master_ball, CHANGE pseudonym pseudonym VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE items_user (items_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8A7F78CFA76ED395 (user_id), INDEX IDX_8A7F78CF6BB0AE84 (items_id), PRIMARY KEY(items_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE items_user ADD CONSTRAINT FK_8A7F78CF6BB0AE84 FOREIGN KEY (items_id) REFERENCES items (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE items_user ADD CONSTRAINT FK_8A7F78CFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_items DROP FOREIGN KEY FK_3DC1215A76ED395');
        $this->addSql('ALTER TABLE user_items DROP FOREIGN KEY FK_3DC1215126F525E');
        $this->addSql('DROP TABLE user_items');
        $this->addSql('ALTER TABLE captured_pokemon DROP times_captured');
        $this->addSql('ALTER TABLE items ADD category_id INT DEFAULT NULL, DROP active');
        $this->addSql('ALTER TABLE items ADD CONSTRAINT FK_E11EE94D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_E11EE94D12469DE2 ON items (category_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD hyper_ball INT DEFAULT NULL, ADD shiny_ball INT DEFAULT NULL, ADD master_ball INT DEFAULT NULL, CHANGE pseudonym pseudonym VARCHAR(50) NOT NULL');
    }
}
