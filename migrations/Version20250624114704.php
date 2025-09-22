<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624114704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql(<<<'SQL'
//            ALTER TABLE captured_pokemon DROP name, DROP type, DROP type2, DROP description, DROP name_en, DROP rarity, DROP poke_id
//        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A43662D721C6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A436610628E28
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7E1A43662D721C6 ON trade
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7E1A436610628E28 ON trade
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD pokemon_trade1_id INT DEFAULT NULL, ADD pokemon_trade2_id INT DEFAULT NULL, DROP trade_poke1_id, DROP trade_poke2_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366AA3B8B7A FOREIGN KEY (pokemon_trade1_id) REFERENCES captured_pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366B88E2494 FOREIGN KEY (pokemon_trade2_id) REFERENCES captured_pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E1A4366AA3B8B7A ON trade (pokemon_trade1_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E1A4366B88E2494 ON trade (pokemon_trade2_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE captured_pokemon ADD name VARCHAR(100) NOT NULL, ADD type VARCHAR(50) NOT NULL, ADD type2 VARCHAR(50) DEFAULT NULL, ADD description VARCHAR(5000) NOT NULL, ADD name_en VARCHAR(50) NOT NULL, ADD rarity VARCHAR(30) NOT NULL, ADD poke_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A4366AA3B8B7A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade DROP FOREIGN KEY FK_7E1A4366B88E2494
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7E1A4366AA3B8B7A ON trade
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7E1A4366B88E2494 ON trade
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD trade_poke1_id INT DEFAULT NULL, ADD trade_poke2_id INT DEFAULT NULL, DROP pokemon_trade1_id, DROP pokemon_trade2_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A43662D721C6 FOREIGN KEY (trade_poke1_id) REFERENCES captured_pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trade ADD CONSTRAINT FK_7E1A436610628E28 FOREIGN KEY (trade_poke2_id) REFERENCES captured_pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E1A43662D721C6 ON trade (trade_poke1_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E1A436610628E28 ON trade (trade_poke2_id)
        SQL);
    }
}
