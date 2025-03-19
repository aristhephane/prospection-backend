<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Token de session (obligatoire, max 255 caractères, format sécurisé)
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le token de session est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "Le token de session ne doit pas dépasser 255 caractères.")]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9]+$/", message: "Le token de session doit être alphanumérique.")]
    private ?string $tokenSession = null;

    // Date de dernière activité (obligatoire)
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de dernière activité est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateDerniereActivite = null;

    // Date d'expiration de la session
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateExpiration = null;


    // Relation ManyToOne avec Utilisateur
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    public function __construct()
    {
        $this->tokenSession = bin2hex(random_bytes(32)); // Génère un token sécurisé unique
        $this->dateDerniereActivite = new \DateTime(); // Date de création automatique
        $this->dateExpiration = (new \DateTime())->modify('+1 hour'); // Expire après 1 heure
    }

    // ---------------------------------------------
    // Getters & Setters
    // ---------------------------------------------

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

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

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

    /**
     * Verifie si la session est expirée
     */
    public function isExpired(): bool
    {
        return $this->dateExpiration !== null && $this->dateExpiration < new \DateTime();
    }
}
