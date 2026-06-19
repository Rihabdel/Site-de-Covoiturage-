<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619183135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet DROP CONSTRAINT fk_2b5ba98c62671590');
        $this->addSql('DROP INDEX uniq_2b5ba98c62671590');
        $this->addSql('ALTER TABLE trajet DROP covoiturage_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet ADD covoiturage_id INT NOT NULL');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT fk_2b5ba98c62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_2b5ba98c62671590 ON trajet (covoiturage_id)');
    }
}
