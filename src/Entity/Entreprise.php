<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $raisonSociale = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 50)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $secteurActivite = null;

    #[ORM\Column(nullable: true)]
    private ?int $tailleEntreprise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteWeb = null;

    /**
     * Relation OneToMany vers FicheEntreprise (cascade éventuelle).
     * orphanRemoval = true afin de supprimer automatiquement 
     * les FicheEntreprise orphelines lorsqu'on les retire de la collection.
     *
     * @var Collection<int, FicheEntreprise>
     */
    #[ORM\OneToMany(targetEntity: FicheEntreprise::class, mappedBy: 'entreprise', orphanRemoval: true)]
    private Collection $ficheEntreprise;

    public function __construct()
    {
        $this->ficheEntreprise = new ArrayCollection();
    }

    // ---------------------------------------------
    // Getters & Setters
    // ---------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getSecteurActivite(): ?string
    {
        return $this->secteurActivite;
    }

    public function setSecteurActivite(string $secteurActivite): static
    {
        $this->secteurActivite = $secteurActivite;
        return $this;
    }

    public function getTailleEntreprise(): ?int
    {
        return $this->tailleEntreprise;
    }

    public function setTailleEntreprise(?int $tailleEntreprise): static
    {
        $this->tailleEntreprise = $tailleEntreprise;
        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;
        return $this;
    }

    // ---------------------------------------------
    // Relation OneToMany ficheEntreprise
    // ---------------------------------------------

    /**
     * @return Collection<int, FicheEntreprise>
     */
    public function getFicheEntreprise(): Collection
    {
        return $this->ficheEntreprise;
    }

    public function addFicheEntreprise(FicheEntreprise $ficheEntreprise): static
    {
        if (!$this->ficheEntreprise->contains($ficheEntreprise)) {
            $this->ficheEntreprise->add($ficheEntreprise);
            $ficheEntreprise->setEntreprise($this);
        }
        return $this;
    }

    public function removeFicheEntreprise(FicheEntreprise $ficheEntreprise): static
    {
        if ($this->ficheEntreprise->removeElement($ficheEntreprise)) {
            // Si la ficheEntrepris est liée à cette entreprise, on l’orphanise
            if ($ficheEntreprise->getEntreprise() === $this) {
                $ficheEntreprise->setEntreprise(null);
            }
        }
        return $this;
    }
}
