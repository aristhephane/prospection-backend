<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Raison sociale obligatoire (max 255 caractères)
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La raison sociale est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La raison sociale ne doit pas dépasser 255 caractères.")]
    private ?string $raisonSociale = null;

    // Adresse obligatoire
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    private ?string $adresse = null;

    // Téléphone obligatoire + format validé
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^(\+?\d{1,3}[-. ]?)?\d{10}$/",
        message: "Le numéro de téléphone n'est pas valide.")]
    private ?string $telephone = null;

    // Email valide, optionnel
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Email(message: "Veuillez entrer une adresse e-mail valide.")]
    private ?string $email = null;

    // Secteur d’activité obligatoire
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le secteur d'activité est obligatoire.")]
    private ?string $secteurActivite = null;

    // Taille entreprise doit être un entier positif, optionnel
    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La taille de l'entreprise doit être un nombre positif.")]
    private ?int $tailleEntreprise = null;

    // URL valide, optionnel
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "Veuillez entrer une URL valide.")]
    private ?string $siteWeb = null;

    /**
     * @ORM\Column(type="boolean")
     */
    // Champ booléen pour archiver l'entreprise
    #[ORM\Column(type: 'boolean')]
    private bool $archive = false;

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

     public function isArchive(): bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;

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
            // Si la ficheEntrepris est liée à cette entreprise, on l’a rend orphelline
            if ($ficheEntreprise->getEntreprise() === $this) {
                $ficheEntreprise->setEntreprise(null);
            }
        }
        return $this;
    }
}
