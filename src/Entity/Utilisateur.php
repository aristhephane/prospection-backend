<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Identifiant unique de l'utilisateur
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom de l'utilisateur (obligatoire, maximum 100 caractères)
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom ne doit pas dépasser 100 caractères.")]
    private ?string $nom = null;

    // Prénom de l'utilisateur (obligatoire, maximum 100 caractères)
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(max: 100, maxMessage: "Le prénom ne doit pas dépasser 100 caractères.")]
    private ?string $prenom = null;

    // Adresse email de l'utilisateur (obligatoire, maximum 180 caractères, unique)
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    private ?string $email = null;

    // Mot de passe sécurisé (obligatoire, minimum 8 caractères, avec exigences de complexité)
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 8 caractères.")]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
        message: "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial."
    )]
    private ?string $password = null;

    // Statut actif/inactif de l'utilisateur
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type("bool", message: "La valeur doit être un booléen.")]
    private bool $actif = true;

    // Date de création de l'utilisateur
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de création est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date de création doit être une instance de DateTimeInterface.")]
    private ?\DateTimeInterface $dateCreation = null;

    // Token de réinitialisation du mot de passe
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    // Date d'expiration du token
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $tokenExpiration = null;

    // Relation ManyToMany avec Role (bidirectionnelle)
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(name: 'user_role')]
    private Collection $roles;

    // Relation OneToMany avec FicheEntreprise (côté propriétaire)
    #[ORM\OneToMany(targetEntity: FicheEntreprise::class, mappedBy: 'creePar')]
    private Collection $ficheEntreprise;

    // Relation OneToMany avec HistoriqueModification (côté propriétaire)
    #[ORM\OneToMany(targetEntity: HistoriqueModification::class, mappedBy: 'utilisateur')]
    private Collection $historiqueModification;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->ficheEntreprise = new ArrayCollection();
        $this->historiqueModification = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    // ---------------------------------------------
    // Implémentation de UserInterface & PasswordAuthenticatedUserInterface
    // ---------------------------------------------

    // Retourne l'identifiant de l'utilisateur (ici, l'email)
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // Retourne la liste des rôles (noms) de l'utilisateur
    public function getRoles(): array
    {
        $roleNames = array_map(fn($role) => $role->getNomRole(), $this->roles->toArray());
        // Ajoute le rôle par défaut ROLE_USER
        $roleNames[] = 'ROLE_USER';
        return array_unique($roleNames);
    }

    // Vérifie si l'utilisateur possède un rôle spécifique
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    // Retourne le mot de passe de l'utilisateur
    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Définit le mot de passe en le hachant pour sécurité
    public function setPassword(string $password): self
    {
        // Hachage du mot de passe avec l'algorithme par défaut
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    // Temporaire en attendant les test (non utilisé dans Symfony moderne)
    public function getSalt(): ?string
    {
        return null;
    }

    // Efface les données sensibles temporairement stockées
    public function eraseCredentials(): void
    {
        // Pas de données sensibles à effacer
    }

    // ---------------------------------------------
    // Getters & Setters pour les attributs de base
    // ---------------------------------------------

    // Retourne l'identifiant unique de l'utilisateur
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne le nom de l'utilisateur
    public function getNom(): ?string
    {
        return $this->nom;
    }

    // Définit le nom de l'utilisateur
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    // Retourne le prénom de l'utilisateur
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    // Définit le prénom de l'utilisateur
    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    // Retourne l'email de l'utilisateur
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Définit l'email de l'utilisateur
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    // Retourne le statut actif/inactif de l'utilisateur
    public function isActif(): bool
    {
        return $this->actif;
    }

    // Définit le statut actif/inactif de l'utilisateur
    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    // Retourne la date de création de l'utilisateur
    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    // Définit la date de création de l'utilisateur
    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    // ---------------------------------------------
    // Gestion du token de réinitialisation du mot de passe
    // ---------------------------------------------

    // Retourne le token de réinitialisation
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    // Définit le token de réinitialisation
    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    // Retourne la date d'expiration du token
    public function getTokenExpiration(): ?\DateTimeInterface
    {
        return $this->tokenExpiration;
    }

    // Définit la date d'expiration du token
    public function setTokenExpiration(?\DateTimeInterface $tokenExpiration): self
    {
        $this->tokenExpiration = $tokenExpiration;
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation ManyToMany avec Role
    // ---------------------------------------------

    // Retourne la collection des rôles associés à l'utilisateur
    public function getRolesAsEntities(): Collection
    {
        return $this->roles;
    }

    // Ajoute un rôle à l'utilisateur
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    // Supprime un rôle de l'utilisateur
    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation OneToMany avec FicheEntreprise
    // ---------------------------------------------

    // Retourne la collection des fiches d'entreprise associées à l'utilisateur
    public function getFicheEntreprise(): Collection
    {
        return $this->ficheEntreprise;
    }

    // Ajoute une fiche d'entreprise à l'utilisateur
    public function addFicheEntreprise(FicheEntreprise $ficheEntreprise): self
    {
        if (!$this->ficheEntreprise->contains($ficheEntreprise)) {
            $this->ficheEntreprise->add($ficheEntreprise);
            $ficheEntreprise->setCreePar($this);
        }
        return $this;
    }

    // Supprime une fiche d'entreprise de l'utilisateur
    public function removeFicheEntreprise(FicheEntreprise $ficheEntreprise): self
    {
        if ($this->ficheEntreprise->removeElement($ficheEntreprise)) {
            // Met à jour le côté propriétaire de la relation
            if ($ficheEntreprise->getCreePar() === $this) {
                $ficheEntreprise->setCreePar(null);
            }
        }
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation OneToMany avec HistoriqueModification
    // ---------------------------------------------

    // Retourne la collection des historiques de modification associés à l'utilisateur
    public function getHistoriqueModification(): Collection
    {
        return $this->historiqueModification;
    }

    // Ajoute un historique de modification à l'utilisateur
    public function addHistoriqueModification(HistoriqueModification $historiqueModification): self
    {
        if (!$this->historiqueModification->contains($historiqueModification)) {
            $this->historiqueModification->add($historiqueModification);
            $historiqueModification->setUtilisateur($this);
        }
        return $this;
    }

    // Supprime un historique de modification de l'utilisateur
    public function removeHistoriqueModification(HistoriqueModification $historiqueModification): self
    {
        if ($this->historiqueModification->removeElement($historiqueModification)) {
            if ($historiqueModification->getUtilisateur() === $this) {
                $historiqueModification->setUtilisateur(null);
            }
        }
        return $this;
    }
}
