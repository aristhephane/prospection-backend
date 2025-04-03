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
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenSession = null;

    // Relation ManyToOne avec Utilisateur
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;
    
    // Date de début de session (correspond à date_debut)
    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: "date_debut")]
    private ?\DateTimeInterface $dateDerniereActivite = null;
    
    // Date de fin de session (correspond à date_fin)
    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: "date_fin", nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;
    
    // Adresse IP
    #[ORM\Column(length: 45, nullable: true, name: "ip_address")]
    private ?string $ipAddress = null;
    
    // User agent
    #[ORM\Column(type: Types::TEXT, nullable: true, name: "user_agent")]
    private ?string $userAgent = null;

    public function __construct()
    {
        $this->tokenSession = bin2hex(random_bytes(32)); // Génère un token sécurisé unique
        $this->dateDerniereActivite = new \DateTime(); // Date de création automatique
        $this->dateExpiration = (new \DateTime())->modify('+8 hours'); // Expire après 8 heures
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

    public function setTokenSession(?string $tokenSession): static
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
    
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }
    
    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        
        return $this;
    }
    
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
    
    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        
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
