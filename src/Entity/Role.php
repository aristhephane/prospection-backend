<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom du rôle obligatoire (max 100 caractères)
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du rôle est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom du rôle ne doit pas dépasser 100 caractères.")]
    private ?string $nomRole = null;

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

    public function getNomRole(): ?string
    {
        return $this->nomRole;
    }

    public function setNomRole(string $nomRole): static
    {
        $this->nomRole = $nomRole;
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
            $utilisateur->addRoleEntity($this);
        }
        return $this;
    }

    /**
     * Supprime un utilisateur de ce rôle.
     */
    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            $utilisateur->removeRoleEntity($this);
        }
        return $this;
    }
}

class Utilisateur
{
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles->removeElement($role);
        return $this;
    }
}
