<?php

namespace App\Entity;

use App\Repository\HistoriqueModificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HistoriqueModificationRepository::class)]
class HistoriqueModification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Date de modification obligatoire
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de modification est
    obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateModification = null;

    // Détails de la modification optionnels, max 2000 caractères
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: "Les détails de la ne ne doivent pas dépasser 2000 caractères.")]
    private ?string $detailsModification = null;

    // Relation ManyToOne avec FicheEntreprise obligatoire
    #[ORM\ManyToOne(inversedBy: 'historiqueModification')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FicheEntreprise $ficheEntreprise = null;

    // Relation ManyToOne avec Utilisateur obligatoire
    #[ORM\ManyToOne(inversedBy: 'historiqueModification')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    // ---------------------------------------------
    // Getters & Setters
    // ----------------------------------------------
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getDetailsModification(): ?string
    {
        return $this->detailsModification;
    }

    public function setDetailsModification(?string $detailsModification): static
    {
        $this->detailsModification = $detailsModification;

        return $this;
    }

    public function getFicheEntreprise(): ?FicheEntreprise
    {
        return $this->ficheEntreprise;
    }

    public function setFicheEntreprise(?FicheEntreprise $ficheEntreprise): static
    {
        $this->ficheEntreprise = $ficheEntreprise;

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
