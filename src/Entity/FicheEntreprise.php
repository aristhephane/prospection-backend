<?php

namespace App\Entity;

use App\Repository\FicheEntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FicheEntrepriseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FicheEntreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Date de visite obligatoire, ne peut pas être dans le futur
    // ni dans plus d'un an
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de visite est obligatoire.")]
    #Assert\LessThanOrEqual("today", message: "La date de visite ne peut pas être dans le futur.")]
    #[Assert\LessThanOrEqual("+1 year", message: "La date de visite ne peut pas être dans plus d'un an.")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateVisite = null;

    // Commentaires optionnels, max 1000 caractères
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "Les commentaires ne doivent pas dépasser 1000 caractères.")]
    private ?string $commentaires = null;

    // Date de création obligatoire
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de création est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateCreation = null;

    // Date de modification optionnelle
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $dateModification = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valide;

    // Relation ManyToOne avec Entreprise obligatoire
    #[ORM\ManyToOne(inversedBy: 'ficheEntreprise')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'ficheEntreprise')]
    private ?Utilisateur $creePar = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $modifiePar = null;

    /**
     * @var Collection<int, HistoriqueModification>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueModification::class, mappedBy: 'ficheEntreprise')]
    private Collection $historiqueModification;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'nouveau';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currentPlace = 'nouveau';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->historiqueModification = new ArrayCollection();
    }

    // ---------------------------------------------
    // Getters & Setters
    // ----------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateVisite(): ?\DateTimeInterface
    {
        return $this->dateVisite;
    }

    public function setDateVisite(\DateTimeInterface $dateVisite): static
    {
        $this->dateVisite = $dateVisite;

        return $this;
    }

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): static
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
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

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getCreePar(): ?Utilisateur
    {
        return $this->creePar;
    }

    public function setCreePar(?Utilisateur $creePar): static
    {
        $this->creePar = $creePar;

        return $this;
    }

    public function getModifiePar(): ?Utilisateur
    {
        return $this->modifiePar;
    }

    public function setModifiePar(?Utilisateur $modifiePar): static
    {
        $this->modifiePar = $modifiePar;

        return $this;
    }

    public function isValide(): bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): self
    {
        $this->valide = $valide;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCurrentPlace(): ?string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(?string $currentPlace): self
    {
        $this->currentPlace = $currentPlace;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection<int, HistoriqueModification>
     */
    public function getHistoriqueModification(): Collection
    {
        return $this->historiqueModification;
    }

    public function addHistoriqueModification(HistoriqueModification $historiqueModification): static
    {
        if (!$this->historiqueModification->contains($historiqueModification)) {
            $this->historiqueModification->add($historiqueModification);
            $historiqueModification->setFicheEntreprise($this);
        }

        return $this;
    }

    public function removeHistoriqueModification(HistoriqueModification $historiqueModification): static
    {
        if ($this->historiqueModification->removeElement($historiqueModification)) {
            // set the owning side to null (unless already changed)
            if ($historiqueModification->getFicheEntreprise() === $this) {
                $historiqueModification->setFicheEntreprise(null);
            }
        }

        return $this;
    }
}
