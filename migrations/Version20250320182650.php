<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320182650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise CHANGE email email VARCHAR(100) DEFAULT NULL, CHANGE site_web site_web VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fiche_entreprise ADD statut VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE session CHANGE date_expiration date_expiration DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL, CHANGE token_expiration token_expiration DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise CHANGE email email VARCHAR(100) DEFAULT \'NULL\', CHANGE site_web site_web VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE fiche_entreprise DROP statut');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE session CHANGE date_expiration date_expiration DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE utilisateur CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\', CHANGE token_expiration token_expiration DATETIME DEFAULT \'NULL\'');
    }
}
