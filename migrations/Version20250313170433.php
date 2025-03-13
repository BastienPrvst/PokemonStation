<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313170433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, friend_a_id INT NOT NULL, friend_b_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_55EEAC61A1A48FB8 (friend_a_id), INDEX IDX_55EEAC61B3112056 (friend_b_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_55EEAC61A1A48FB8 FOREIGN KEY (friend_a_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_55EEAC61B3112056 FOREIGN KEY (friend_b_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_55EEAC61A1A48FB8');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_55EEAC61B3112056');
        $this->addSql('DROP TABLE friendship');
    }
}
