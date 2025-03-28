<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom de la permission obligatoire (max 100 caractères)
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom de la permission est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom de la permission ne doit pas dépasser 100 caractères.")]
    private ?string $nomPermission = null;

    // Description optionnelle, max 1000 caractères
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser 1000 caractères.")]
    private ?string $description = null;

    /**
     * @var Collection<int, Role>
     */
    // ManyToMany avec Role
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'permissions')]
    //  #[ORM\JoinTable(name: 'role_permission')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    // ---------------------------------------------
    // Getters & Setters
    // ----------------------------------------------
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addPermission($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }

        return $this;
    }

    public function getNomPermission(): ?string
    {
        return $this->nomPermission;
    }

    public function setNomPermission(string $nomPermission): static
    {
        $this->nomPermission = $nomPermission;

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
}
