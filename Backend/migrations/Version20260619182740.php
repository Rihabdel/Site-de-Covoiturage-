<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619182740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet ADD covoiturage_id INT NOT NULL');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2B5BA98C62671590 ON trajet (covoiturage_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT FK_2B5BA98C62671590');
        $this->addSql('DROP INDEX UNIQ_2B5BA98C62671590');
        $this->addSql('ALTER TABLE trajet DROP covoiturage_id');
    }
}
