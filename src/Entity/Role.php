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
    // ManyToMany avec Permission
    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'roles')]
    #[ORM\JoinTable(name: 'role_permission')]
    private Collection $permissions;

    /**
     * @var Collection<int, Utilisateur>
     */
    // ManyToMany avec Utilisateur
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'roles')]
    private Collection $user_role;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->user_role = new ArrayCollection();
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
     * @return Collection<int, Utilisateur>
     */
    public function getUserRole(): Collection
    {
        return $this->user_role;
    }

    public function addUserRole(Utilisateur $userRole): static
    {
        if (!$this->user_role->contains($userRole)) {
            $this->user_role->add($userRole);
            $userRole->addRole($this);
        }

        return $this;
    }

    public function removeUserRole(Utilisateur $userRole): static
    {
        if ($this->user_role->removeElement($userRole)) {
            $userRole->removeRole($this);
        }

        return $this;
    }
}
