<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $latitudeDepart = null;

    #[ORM\Column]
    private ?float $latitudeArrivee = null;

    #[ORM\Column]
    private ?float $longitudeDepart = null;

    #[ORM\Column]
    private ?float $longitudeArrivee = null;

    #[ORM\Column(length: 100)]
    private ?string $adresseDepart = null;

    #[ORM\Column(length: 100)]
    private ?string $adresseArrivee = null;

    #[ORM\Column]
    private ?float $distance = null;

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;

    #[ORM\OneToOne(mappedBy: 'trajet')]
    private ?Covoiturage $covoiturage = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitudeDepart(): ?float
    {
        return $this->latitudeDepart;
    }

    public function setLatitudeDepart(float $latitudeDepart): static
    {
        $this->latitudeDepart = $latitudeDepart;

        return $this;
    }

    public function getLatitudeArrivee(): ?float
    {
        return $this->latitudeArrivee;
    }

    public function setLatitudeArrivee(float $latitudeArrivee): static
    {
        $this->latitudeArrivee = $latitudeArrivee;

        return $this;
    }

    public function getLongitudeDepart(): ?float
    {
        return $this->longitudeDepart;
    }

    public function setLongitudeDepart(float $longitudeDepart): static
    {
        $this->longitudeDepart = $longitudeDepart;

        return $this;
    }

    public function getLongitudeArrivee(): ?float
    {
        return $this->longitudeArrivee;
    }

    public function setLongitudeArrivee(float $longitudeArrivee): static
    {
        $this->longitudeArrivee = $longitudeArrivee;

        return $this;
    }

    public function getAdresseDepart(): ?string
    {
        return $this->adresseDepart;
    }

    public function setAdresseDepart(string $adresseDepart): static
    {
        $this->adresseDepart = $adresseDepart;

        return $this;
    }

    public function getAdresseArrivee(): ?string
    {
        return $this->adresseArrivee;
    }

    public function setAdresseArrivee(string $adresseArrivee): static
    {
        $this->adresseArrivee = $adresseArrivee;

        return $this;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): static
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }
    

public function getCovoiturage(): ?Covoiturage
{
    return $this->covoiturage;
}

public function setCovoiturage(?Covoiturage $covoiturage): static
{
    $this->covoiturage = $covoiturage;

    return $this;
}


}
