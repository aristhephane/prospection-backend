<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomPermission = null;

    public function getId(): ?int
    {
        return $this->id;
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
}
