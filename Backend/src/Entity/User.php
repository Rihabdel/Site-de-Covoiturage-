<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['user:read'])]
    #[ORM\Column]
    private ?int $id = null;
    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;
    #[Groups(['user:read','covoiturage:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nom = null;
    #[Groups(['user:read','covoiturage:read'])]
    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.', groups: ['create', 'update'])]
    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;
    #[Groups(['user:read', 'covoiturage:read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom = null;
    #[Groups(['user:read', 'user:update'])]
   #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;
    #[Groups(['user:read','covoiturage:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;
   
    #[ORM\Column(nullable: true)]
    private ?int $credits = null;
    
    #[ORM\Column(nullable: true)]
    private ?int $telephone = null;
   
    #[ORM\Column]
    private ?bool $isConducteur = null;
    
    #[ORM\Column]
    private ?bool $isPassager = null;

    /**
     * @var Collection<int, Vehicule>
     * @Groups(['user:read'])
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'proprietaire')]
    private Collection $vehicules;

    /**
     * @var Collection<int, Covoiturage>
     */
    #[ORM\OneToMany(targetEntity: Covoiturage::class, mappedBy: 'chauffeur')]
    private Collection $trajetsProposes;

    /**
     * @var Collection<int, Covoiturage>
     */
    #[ORM\ManyToMany(targetEntity: Covoiturage::class, mappedBy: 'passagers')]
    private Collection $trajetsParticipes;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'auteur')]
    private Collection $avis;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $apiToken = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?int $note = null;

    public function __construct()
    {
        $this->vehicules = new ArrayCollection();
        $this->trajetsProposes = new ArrayCollection();
        $this->trajetsParticipes = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }
  #[Groups(['covoiturage:read'])]
    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(?int $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(?int $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isConducteur(): ?bool
    {
        return $this->isConducteur;
    }

    public function setIsConducteur(bool $isConducteur): static
    {
        $this->isConducteur = $isConducteur;

        return $this;
    }
  #[Groups(['covoiturage:read'])]
    public function isPassager(): ?bool
    {
        return $this->isPassager;
    }

    public function setIsPassager(bool $isPassager): static
    {
        $this->isPassager = $isPassager;

        return $this;
    }
     
    /**
     * @return Collection<int, Vehicule>
     */
   
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicule $vehicule): static
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules->add($vehicule);
            $vehicule->setProprietaire($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): static
    {
        if ($this->vehicules->removeElement($vehicule)) {
            // set the owning side to null (unless already changed)
            if ($vehicule->getProprietaire() === $this) {
                $vehicule->setProprietaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Covoiturage>
     */
    public function getTrajetsProposes(): Collection
    {
        return $this->trajetsProposes;
    }

    public function addTrajetsPropose(Covoiturage $trajetsPropose): static
    {
        if (!$this->trajetsProposes->contains($trajetsPropose)) {
            $this->trajetsProposes->add($trajetsPropose);
            $trajetsPropose->setChauffeur($this);
        }

        return $this;
    }

    public function removeTrajetsPropose(Covoiturage $trajetsPropose): static
    {
        if ($this->trajetsProposes->removeElement($trajetsPropose)) {
            // set the owning side to null (unless already changed)
            if ($trajetsPropose->getChauffeur() === $this) {
                $trajetsPropose->setChauffeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Covoiturage>
     */
    public function getTrajetsParticipes(): Collection
    {
        return $this->trajetsParticipes;
    }

    public function addTrajetsParticipe(Covoiturage $trajetsParticipe): static
    {
        if (!$this->trajetsParticipes->contains($trajetsParticipe)) {
            $this->trajetsParticipes->add($trajetsParticipe);
            $trajetsParticipe->addPassager($this);
        }

        return $this;
    }

    public function removeTrajetsParticipe(Covoiturage $trajetsParticipe): static
    {
        if ($this->trajetsParticipes->removeElement($trajetsParticipe)) {
            $trajetsParticipe->removePassager($this);
        }

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
            $avi->setAuteur($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getAuteur() === $this) {
                $avi->setAuteur(null);
            }
        }

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
     #[Groups(['user:read'])]
    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): static
    {
        $this->note = $note;

        return $this;
    }
}
