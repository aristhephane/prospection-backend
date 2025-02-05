<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $tokenSession = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDerniereActivite = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenSession(): ?string
    {
        return $this->tokenSession;
    }

    public function setTokenSession(string $tokenSession): static
    {
        $this->tokenSession = $tokenSession;

        return $this;
    }

    public function getDateDerniereActivite(): ?\DateTimeInterface
    {
        return $this->dateDerniereActivite;
    }

    public function setDateDerniereActivite(\DateTimeInterface $dateDerniereActivite): static
    {
        $this->dateDerniereActivite = $dateDerniereActivite;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
