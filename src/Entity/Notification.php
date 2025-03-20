<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: "notification")]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre de la notification est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le titre ne doit pas dépasser 255 caractères.")]
    private ?string $titre = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "Le contenu de la notification est obligatoire.")]
    private ?string $contenu = null;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "Le type de la notification est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le type ne doit pas dépasser 50 caractères.")]
    private ?string $type = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de création de la notification est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "notifications")]
    #[Assert\NotNull(message: "La notification doit être associée à un utilisateur.")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: "boolean")]
    private bool $isRead = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime(); // Date de création automatique
        $this->isRead = false; // Par défaut, la notification n'est pas lue
    }

    // ---------------------------------------------
    // Getters & Setters
    // ---------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }
}
