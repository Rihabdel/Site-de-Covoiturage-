<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260618080733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE covoiturage_user (covoiturage_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (covoiturage_id, user_id))');
        $this->addSql('CREATE INDEX IDX_F862CC4962671590 ON covoiturage_user (covoiturage_id)');
        $this->addSql('CREATE INDEX IDX_F862CC49A76ED395 ON covoiturage_user (user_id)');
        $this->addSql('ALTER TABLE covoiturage_user ADD CONSTRAINT FK_F862CC4962671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE covoiturage_user ADD CONSTRAINT FK_F862CC49A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD auteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE avis ADD covoiturage_id INT NOT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF060BB6FE6 FOREIGN KEY (auteur_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF062671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_8F91ABF060BB6FE6 ON avis (auteur_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF062671590 ON avis (covoiturage_id)');
        $this->addSql('ALTER TABLE covoiturage ADD chauffeur_id INT NOT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD vehicule_id INT NOT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E8985C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E894A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_28C79E8985C0B3BE ON covoiturage (chauffeur_id)');
        $this->addSql('CREATE INDEX IDX_28C79E894A4A3511 ON covoiturage (vehicule_id)');
        $this->addSql('ALTER TABLE vehicule ADD proprietaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_292FFF1D76C50E4A ON vehicule (proprietaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage_user DROP CONSTRAINT FK_F862CC4962671590');
        $this->addSql('ALTER TABLE covoiturage_user DROP CONSTRAINT FK_F862CC49A76ED395');
        $this->addSql('DROP TABLE covoiturage_user');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF060BB6FE6');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF062671590');
        $this->addSql('DROP INDEX IDX_8F91ABF060BB6FE6');
        $this->addSql('DROP INDEX IDX_8F91ABF062671590');
        $this->addSql('ALTER TABLE avis DROP auteur_id');
        $this->addSql('ALTER TABLE avis DROP covoiturage_id');
        $this->addSql('ALTER TABLE covoiturage DROP CONSTRAINT FK_28C79E8985C0B3BE');
        $this->addSql('ALTER TABLE covoiturage DROP CONSTRAINT FK_28C79E894A4A3511');
        $this->addSql('DROP INDEX IDX_28C79E8985C0B3BE');
        $this->addSql('DROP INDEX IDX_28C79E894A4A3511');
        $this->addSql('ALTER TABLE covoiturage DROP chauffeur_id');
        $this->addSql('ALTER TABLE covoiturage DROP vehicule_id');
        $this->addSql('ALTER TABLE vehicule DROP CONSTRAINT FK_292FFF1D76C50E4A');
        $this->addSql('DROP INDEX IDX_292FFF1D76C50E4A');
        $this->addSql('ALTER TABLE vehicule DROP proprietaire_id');
    }
}
