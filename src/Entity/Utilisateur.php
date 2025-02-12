<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;


    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

     /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    // Relation ManyToMany vers Role (côté propriétaire)
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_role')] 
    private Collection $roles;

    /**
     * @var Collection<int, FicheEntreprise>
     */
    #[ORM\OneToMany(targetEntity: FicheEntreprise::class, mappedBy: 'creePar')]
    private Collection $ficheEntreprise;

    /**
     * @var Collection<int, HistoriqueModification>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueModification::class, mappedBy: 'utilisateur')]
    private Collection $historiqueModification;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->ficheEntreprise = new ArrayCollection();
        $this->historiqueModification = new ArrayCollection();
    }

    // ---------------------------------------------
    // Implémentation UserInterface & PasswordAuthenticatedUserInterface
    // ---------------------------------------------

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roleNames = [];
        foreach ($this->roles as $roleEntity) {
            $roleNames[] = $roleEntity->getNomRole();
        }

        $roleNames[] = 'ROLE_USER';
        return array_unique($roleNames);
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
       
    }

     public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    // ---------------------------------------------
    // Getters/Setters pour les champs de base
    // ---------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRolesAsEntities(): Collection
    {
       
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    /**
     * @return Collection<int, FicheEntreprise>
     */
    public function getFicheEntreprise(): Collection
    {
        return $this->ficheEntreprise;
    }

    public function addFicheEntreprise(FicheEntreprise $ficheEntreprise): static
    {
        if (!$this->ficheEntreprise->contains($ficheEntreprise)) {
            $this->ficheEntreprise->add($ficheEntreprise);
            $ficheEntreprise->setCreePar($this);
        }

        return $this;
    }

    public function removeFicheEntreprise(FicheEntreprise $ficheEntreprise): static
    {
        if ($this->ficheEntreprise->removeElement($ficheEntreprise)) {
            // set the owning side to null (unless already changed)
            if ($ficheEntreprise->getCreePar() === $this) {
                $ficheEntreprise->setCreePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueModification>
     */
    public function getHistoriqueModification(): Collection
    {
        return $this->historiqueModification;
    }

    public function addHistoriqueModification(HistoriqueModification $historiqueModification): static
    {
        if (!$this->historiqueModification->contains($historiqueModification)) {
            $this->historiqueModification->add($historiqueModification);
            $historiqueModification->setUtilisateur($this);
        }

        return $this;
    }

    public function removeHistoriqueModification(HistoriqueModification $historiqueModification): static
    {
        if ($this->historiqueModification->removeElement($historiqueModification)) {
            // set the owning side to null (unless already changed)
            if ($historiqueModification->getUtilisateur() === $this) {
                $historiqueModification->setUtilisateur(null);
            }
        }

        return $this;
    }
}
