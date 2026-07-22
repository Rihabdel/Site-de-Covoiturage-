<?php

namespace App\Entity;
use App\Enum\Statut;
use App\Repository\CovoiturageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CovoiturageRepository::class)]
class Covoiturage
{
    #[Groups(['covoiturage:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
#[Groups(['covoiturage:read'])]
    #[ORM\Column(length: 100)]
    private ?string $adresseDepart = null;
 #[Groups(['covoiturage:read'])]
    #[ORM\Column(length: 100)]
    private ?string $adresseArrivee = null;
     #[Groups(['covoiturage:read'])]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDepart = null;
 #[Groups(['covoiturage:read'])]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateArrivee = null;
#[Groups(['covoiturage:read'])]
    #[ORM\Column]
    private ?float $prix = null;
#[Groups(['covoiturage:read'])]
    #[ORM\Column]
    private ?int $placeDisponible = null;
#[Groups(['covoiturage:read'])]
    #[ORM\Column]
    private ?bool $voyageEcologique = null;
#[Groups(['covoiturage:read'])]
    #[ORM\Column(type: 'string', enumType: Statut::class)]
    private ?Statut $statut =  Statut::PENDING;
   #[Groups(['covoiturage:read'])]
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

  
    #[Groups(['covoiturage:read'])]
    #[ORM\OneToOne(inversedBy: 'covoiturage', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
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

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(\DateTimeInterface $dateArrivee): static
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
    #[Groups(['covoiturage:read'])]
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
     #[Groups(['covoiturage:read'])]
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
    public function isValidatedByPassenger(): bool
    {
        foreach ($this->avis as $avis) {
            if ($avis->getType() === 'passager' && $avis->isValidated()) {
                return true;
            }
        }
        return false;
    }
}
