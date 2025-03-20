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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $nom = null;

    // Description optionnelle, max 1000 caractères
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser 1000 caractères.")]
    private ?string $description = null;

    /**
     * @var Collection<int, Permission>
     */
    // Relation ManyToMany avec Permission
    #[ORM\ManyToMany(targetEntity: Permission::class, mappedBy: 'roles')]
    private Collection $permissions;

    /**
     * @var Collection<int, Utilisateur>
     */
    // Relation ManyToMany avec Utilisateur (inversé de celle définie dans Utilisateur)
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'roleEntities')]
    private Collection $utilisateurs;

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

    // Utilisez $nom au lieu de $name
    public function __toString(): string
    {
        return $this->nom ?: '';
    }
}

