<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;


#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[Groups(["vehicule:read"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Groups(["vehicule:read", "covoiturage:read"])]
    #[ORM\Column(length: 50)]
    private ?string $numeroImmatriculation = null;
    #[Groups(["vehicule:read", "covoiturage:read"])]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateImmatriculation = null;
    #[Groups(["vehicule:read", "covoiturage:read"])]
    #[ORM\Column(length: 50)]
    private ?string $modele = null;
    #[Groups(["vehicule:read", "covoiturage:read"])]
    #[ORM\Column(length: 50)]
    private ?string $couleur = null;
    #[Groups(["vehicule:read"])]
    #[ORM\Column(length: 50)]
    private ?string $marque = null;
    #[Groups(["vehicule:read", "covoiturage:read"])]
    #[ORM\Column(length: 50)]
    private ?string $energie = null;
    
    #[ORM\ManyToOne(inversedBy: 'vehicules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $proprietaire = null;

    /**
     * @var Collection<int, Covoiturage>
     */
    #[ORM\OneToMany(targetEntity: Covoiturage::class, mappedBy: 'vehicule')]
    private Collection $covoiturages;

    public function __construct()
    {
        $this->covoiturages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
   
    public function getNumeroImmatriculation(): ?string
    {
        return $this->numeroImmatriculation;
    }

    public function setNumeroImmatriculation(string $numeroImmatriculation): static
    {
        $this->numeroImmatriculation = $numeroImmatriculation;

        return $this;
    }
    
    public function getDateImmatriculation(): ?\DateTime
    {
        return $this->dateImmatriculation;
    }

    public function setDateImmatriculation(\DateTime $dateImmatriculation): static
    {
        $this->dateImmatriculation = $dateImmatriculation;

        return $this;
    }
    
    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }
  
    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }
   
    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }
  
    public function getEnergie(): ?string
    {
        return $this->energie;
    }

    public function setEnergie(string $energie): static
    {
        $this->energie = $energie;

        return $this;
    }

    public function getProprietaire(): ?User
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?User $proprietaire): static
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    /**
     * @return Collection<int, Covoiturage>
     */
    public function getCovoiturages(): Collection
    {
        return $this->covoiturages;
    }

    public function addCovoiturage(Covoiturage $covoiturage): static
    {
        if (!$this->covoiturages->contains($covoiturage)) {
            $this->covoiturages->add($covoiturage);
            $covoiturage->setVehicule($this);
        }

        return $this;
    }

    public function removeCovoiturage(Covoiturage $covoiturage): static
    {
        if ($this->covoiturages->removeElement($covoiturage)) {
            // set the owning side to null (unless already changed)
            if ($covoiturage->getVehicule() === $this) {
                $covoiturage->setVehicule(null);
            }
        }

        return $this;
    }
}
