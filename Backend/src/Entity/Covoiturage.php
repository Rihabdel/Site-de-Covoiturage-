<?php

namespace App\Entity;
use App\Enum\Statut;
use App\Repository\CovoiturageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CovoiturageRepository::class)]
class Covoiturage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $adresseDepart = null;

    #[ORM\Column(length: 100)]
    private ?string $adresseArrivee = null;

    #[ORM\Column]
    private ?\DateTime $dateDepart = null;

    #[ORM\Column]
    private ?\DateTime $dateArrivee = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $placeDisponible = null;

    #[ORM\Column]
    private ?bool $voyageEcologique = null;

    #[ORM\Column(type: 'string', enumType: Statut::class)]
    private ?Statut $statut =  Statut::PENDING;

    #[ORM\ManyToOne(inversedBy: 'trajetsProposes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $chauffeur = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'trajetsParticipes')]
    private Collection $passagers;

    #[ORM\ManyToOne(inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicule $vehicule = null;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'covoiturage')]
    private Collection $avis;

    #[ORM\OneToOne(mappedBy: 'covoiturage', cascade: ['persist', 'remove'])]
    private ?Trajet $trajet = null;

    public function __construct()
    {
        $this->passagers = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateDepart(): ?\DateTime
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTime $dateDepart): static
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getDateArrivee(): ?\DateTime
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(\DateTime $dateArrivee): static
    {
        $this->dateArrivee = $dateArrivee;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPlaceDisponible(): ?int
    {
        return $this->placeDisponible;
    }

    public function setPlaceDisponible(int $placeDisponible): static
    {
        $this->placeDisponible = $placeDisponible;

        return $this;
    }

    public function isVoyageEcologique(): ?bool
    {
        return $this->voyageEcologique;
    }

    public function setVoyageEcologique(bool $voyageEcologique): static
    {
        $this->voyageEcologique = $voyageEcologique;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(Statut $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getChauffeur(): ?User
    {
        return $this->chauffeur;
    }

    public function setChauffeur(?User $chauffeur): static
    {
        $this->chauffeur = $chauffeur;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPassagers(): Collection
    {
        return $this->passagers;
    }

    public function addPassager(User $passager): static
    {
        if (!$this->passagers->contains($passager)) {
            $this->passagers->add($passager);
        }

        return $this;
    }

    public function removePassager(User $passager): static
    {
        $this->passagers->removeElement($passager);

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setCovoiturage($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getCovoiturage() === $this) {
                $avi->setCovoiturage(null);
            }
        }

        return $this;
    }

    public function getTrajet(): ?Trajet
    {
        return $this->trajet;
    }

    public function setTrajet(Trajet $trajet): static
    {
        // set the owning side of the relation if necessary
        if ($trajet->getCovoiturage() !== $this) {
            $trajet->setCovoiturage($this);
        }

        $this->trajet = $trajet;

        return $this;
    }
}
