<?php

namespace App\Entity;

use App\Repository\HistoriqueModificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueModificationRepository::class)]
class HistoriqueModification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $detailsModification = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueModification')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FicheEntreprise $ficheEntreprise = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueModification')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

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
