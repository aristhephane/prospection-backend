<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Message de la notification
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le message de la notification est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le message ne doit pas dépasser 255 caractères.")]
    private ?string $message = null;

    // Date de création de la notification
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de création de la notification est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateCreated = null;

    // Indique si la notification a été lue
    #[ORM\Column(type: 'boolean', options: ["default" => false])]
    #[Assert\Type("bool", message: "Le champ doit être un booléen.")]
    private bool $isRead = false;

    // Relation ManyToOne avec Utilisateur
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'notifications')]
    #[Assert\NotNull(message: "La notification doit être associée à un utilisateur.")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $user = null;

    public function __construct()
    {
        $this->dateCreated = new \DateTime(); // Date de création automatique
    }

    // ---------------------------------------------
    // Getters & Setters
    // ---------------------------------------------
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;
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

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): self
    {
        $this->user = $user;
        return $this;
    }
}
