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
#[ORM\Table(name: "utilisateur")]
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

    // Mot de passe sécurisé (obligatoire, minimum 8 caractères avec exigences de complexité)
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

    // Token de réinitialisation du mot de passe (optionnel)
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    // Date d'expiration du token (optionnel)
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $tokenExpiration = null;

    // Relation ManyToMany avec Role (utilisation de la propriété "roles")
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(name: 'user_role')]
    private Collection $roles;

    // Relation OneToMany avec FicheEntreprise (côté propriétaire)
    #[ORM\OneToMany(targetEntity: FicheEntreprise::class, mappedBy: 'creePar')]
    private Collection $ficheEntreprise;

    // Relation OneToMany avec HistoriqueModification (côté propriétaire)
    #[ORM\OneToMany(targetEntity: \App\Entity\HistoriqueModification::class, mappedBy: 'utilisateur')]
    private Collection $historiqueModification;

    // Relation OneToMany avec Session
    #[ORM\OneToMany(targetEntity: \App\Entity\Session::class, mappedBy: 'utilisateur')]
    private Collection $sessions;

    // Relation OneToMany avec Notification
    #[ORM\OneToMany(targetEntity: \App\Entity\Notification::class, mappedBy: 'utilisateur')]
    private Collection $notifications;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->ficheEntreprise = new ArrayCollection();
        $this->historiqueModification = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->sessions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    // ---------------------------------------------
    // Méthodes de UserInterface et PasswordAuthenticatedUserInterface
    // ---------------------------------------------

    // Retourne l'identifiant uniquely identifying the user (ici l'email)
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // Retourne les noms des rôles (en ajoutant ROLE_USER par défaut)
    public function getRoles(): array
    {
        $roleNames = array_map(fn($role) => $role->getNomRole(), $this->roles->toArray());
        $roleNames[] = 'ROLE_USER';
        return array_unique($roleNames);
    }

    // Retourne la collection complète des entités Role associées
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    // Ajoute un Role à l'utilisateur et assure la réciprocité
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addUtilisateur($this);
        }
        return $this;
    }

    // Supprime un Role de l'utilisateur et assure la réciprocité
    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removeUtilisateur($this);
        }
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Définit le mot de passe (en le hachant)
    public function setPassword(string $password): self
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    // ---------------------------------------------
    // Getters & Setters pour les attributs de base
    // ---------------------------------------------
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getTokenExpiration(): ?\DateTimeInterface
    {
        return $this->tokenExpiration;
    }

    public function setTokenExpiration(?\DateTimeInterface $tokenExpiration): self
    {
        $this->tokenExpiration = $tokenExpiration;
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation OneToMany avec FicheEntreprise
    // ---------------------------------------------
    public function getFicheEntreprise(): Collection
    {
        return $this->ficheEntreprise;
    }

    public function addFicheEntreprise(FicheEntreprise $ficheEntreprise): self
    {
        if (!$this->ficheEntreprise->contains($ficheEntreprise)) {
            $this->ficheEntreprise->add($ficheEntreprise);
            $ficheEntreprise->setCreePar($this);
        }
        return $this;
    }

    public function removeFicheEntreprise(FicheEntreprise $ficheEntreprise): self
    {
        if ($this->ficheEntreprise->removeElement($ficheEntreprise)) {
            if ($ficheEntreprise->getCreePar() === $this) {
                $ficheEntreprise->setCreePar(null);
            }
        }
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation OneToMany avec HistoriqueModification
    // ---------------------------------------------
    public function getHistoriqueModification(): Collection
    {
        return $this->historiqueModification;
    }

    public function addHistoriqueModification(HistoriqueModification $historiqueModification): self
    {
        if (!$this->historiqueModification->contains($historiqueModification)) {
            $this->historiqueModification->add($historiqueModification);
            $historiqueModification->setUtilisateur($this);
        }
        return $this;
    }

    public function removeHistoriqueModification(HistoriqueModification $historiqueModification): self
    {
        if ($this->historiqueModification->removeElement($historiqueModification)) {
            if ($historiqueModification->getUtilisateur() === $this) {
                $historiqueModification->setUtilisateur(null);
            }
        }
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation OneToMany avec Session
    // ---------------------------------------------
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(\App\Entity\Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setUtilisateur($this);
        }
        return $this;
    }

    public function removeSession(\App\Entity\Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            if ($session->getUtilisateur() === $this) {
                $session->setUtilisateur(null);
            }
        }
        return $this;
    }

    // ---------------------------------------------
    // Gestion de la relation OneToMany avec Notification
    // ---------------------------------------------
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(\App\Entity\Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUtilisateur($this);
        }
        return $this;
    }

    public function removeNotification(\App\Entity\Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUtilisateur() === $this) {
                $notification->setUtilisateur(null);
            }
        }
        return $this;
    }
}
