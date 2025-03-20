<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320152857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise CHANGE email email VARCHAR(100) DEFAULT NULL, CHANGE site_web site_web VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('DROP INDEX IDX_BF5476CAA76ED395 ON notification');
        $this->addSql('ALTER TABLE notification ADD contenu LONGTEXT NOT NULL, ADD type VARCHAR(50) NOT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE user_id utilisateur_id INT NOT NULL, CHANGE message titre VARCHAR(255) NOT NULL, CHANGE date_created created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAFB88E14F ON notification (utilisateur_id)');
        $this->addSql('ALTER TABLE role ADD nom VARCHAR(255) NOT NULL, DROP nom_role');
        $this->addSql('ALTER TABLE session CHANGE date_expiration date_expiration DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE token_expiration token_expiration DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise CHANGE email email VARCHAR(100) DEFAULT \'NULL\', CHANGE site_web site_web VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('DROP INDEX IDX_BF5476CAFB88E14F ON notification');
        $this->addSql('ALTER TABLE notification DROP contenu, DROP type, CHANGE is_read is_read TINYINT(1) DEFAULT 0 NOT NULL, CHANGE utilisateur_id user_id INT NOT NULL, CHANGE titre message VARCHAR(255) NOT NULL, CHANGE created_at date_created DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('ALTER TABLE role ADD nom_role VARCHAR(100) NOT NULL, DROP nom');
        $this->addSql('ALTER TABLE session CHANGE date_expiration date_expiration DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE utilisateur CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE token_expiration token_expiration DATETIME DEFAULT \'NULL\'');
    }
}
