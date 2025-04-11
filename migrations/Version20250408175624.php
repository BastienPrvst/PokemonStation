<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408175624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, author VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_items ADD CONSTRAINT FK_3DC1215A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_items ADD CONSTRAINT FK_3DC1215126F525E FOREIGN KEY (item_id) REFERENCES items (id)');
        $this->addSql('CREATE INDEX IDX_3DC1215A76ED395 ON user_items (user_id)');
        $this->addSql('CREATE INDEX IDX_3DC1215126F525E ON user_items (item_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE news');
        $this->addSql('ALTER TABLE user_items DROP FOREIGN KEY FK_3DC1215A76ED395');
        $this->addSql('ALTER TABLE user_items DROP FOREIGN KEY FK_3DC1215126F525E');
        $this->addSql('DROP INDEX IDX_3DC1215A76ED395 ON user_items');
        $this->addSql('DROP INDEX IDX_3DC1215126F525E ON user_items');
    }
}
