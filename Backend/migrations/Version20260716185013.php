<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716185013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet ADD ville_depart VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE trajet ADD ville_arrivee VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD accepte_animaux BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD non_fumeur BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD description_preference TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD musique BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trajet DROP ville_depart');
        $this->addSql('ALTER TABLE trajet DROP ville_arrivee');
        $this->addSql('ALTER TABLE "user" DROP accepte_animaux');
        $this->addSql('ALTER TABLE "user" DROP non_fumeur');
        $this->addSql('ALTER TABLE "user" DROP description_preference');
        $this->addSql('ALTER TABLE "user" DROP musique');
    }
}
