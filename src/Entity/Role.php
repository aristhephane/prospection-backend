<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: "role")]
class Role
{
    // Les codes des rôles spécifiques
    public const ROLE_ADMIN = 'administrateur';
    public const ROLE_PROSPECTION = 'prospection';
    public const ROLE_RESPONSABLE = 'responsable';
    public const ROLE_ACADEMIQUE = 'academique';
    public const ROLE_SECRETARIAT = 'secretariat';
    public const ROLE_ORIENTATION = 'orientation';
    public const ROLE_ENSEIGNANT = 'enseignant';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $nom = null;

    // Description optionnelle, max 1000 caractères
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser 1000 caractères.")]
    private ?string $description = null;

    /**
     * @var Collection<int, Permission>
     */
    // Relation ManyToMany avec Permission
    #[ORM\ManyToMany(targetEntity: Permission::class, mappedBy: 'roleEntities')]
    private Collection $permissions;

    /**
     * @var Collection<int, Utilisateur>
     */
    // Relation ManyToMany avec Utilisateur (inversé de celle définie dans Utilisateur)
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'roleEntities')]
    private Collection $utilisateurs;

    #[ORM\Column(type: "boolean")]
    private bool $accesRapports = false;

    #[ORM\Column(type: "boolean")]
    private bool $modificationDonnees = false;

    #[ORM\Column(type: "boolean")]
    private bool $administrationSysteme = false;

    #[ORM\Column(type: "string", length: 50)]
    private string $typeAccesFiches = 'Lecture';  // 'Lecture' ou 'Lecture/Écriture'

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
    }

    // ---------------------------------------------
    // Getters & Setters
    // ----------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }
        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        $this->permissions->removeElement($permission);
        return $this;
    }

    /**
     * Retourne la collection d'utilisateurs associés à ce rôle.
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    /**
     * Ajoute un utilisateur à ce rôle.
     */
    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->addRole($this);
        }
        return $this;
    }

    /**
     * Supprime un utilisateur de ce rôle.
     */
    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            $utilisateur->removeRole($this);
        }
        return $this;
    }

    public function isAccesRapports(): bool
    {
        return $this->accesRapports;
    }

    public function setAccesRapports(bool $accesRapports): static
    {
        $this->accesRapports = $accesRapports;
        return $this;
    }

    public function isModificationDonnees(): bool
    {
        return $this->modificationDonnees;
    }

    public function setModificationDonnees(bool $modificationDonnees): static
    {
        $this->modificationDonnees = $modificationDonnees;
        return $this;
    }

    public function isAdministrationSysteme(): bool
    {
        return $this->administrationSysteme;
    }

    public function setAdministrationSysteme(bool $administrationSysteme): static
    {
        $this->administrationSysteme = $administrationSysteme;
        return $this;
    }

    public function getTypeAccesFiches(): string
    {
        return $this->typeAccesFiches;
    }

    public function setTypeAccesFiches(string $typeAccesFiches): static
    {
        if ($typeAccesFiches === 'Lecture' || $typeAccesFiches === 'Lecture/Écriture') {
            $this->typeAccesFiches = $typeAccesFiches;
        }
        return $this;
    }

    public function isLectureEcritureFiches(): bool
    {
        return $this->typeAccesFiches === 'Lecture/Écriture';
    }

    // Utilisez $nom au lieu de $name
    public function __toString(): string
    {
        return $this->nom ?: '';
    }
}

