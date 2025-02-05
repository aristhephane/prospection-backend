<?php

namespace App\Entity;

use App\Repository\FicheEntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FicheEntrepriseRepository::class)]
class FicheEntreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateVisite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaires = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

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

    public function __construct()
    {
        $this->historiqueModification = new ArrayCollection();
    }

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
