<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\Utilisateur;
use App\Repository\SessionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SessionAuthService
{
    private const SESSION_COOKIE_NAME = 'PROSPECTION_SESSION';
    private const SESSION_DURATION = '+8 hours';
    
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionRepository $sessionRepository,
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RequestStack $requestStack
    ) {
    }
    
    /**
     * Authentifier un utilisateur et créer une session
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->utilisateurRepository->findOneBy(['email' => $email]);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return null;
        }
        
        // Créer une nouvelle session
        $session = new Session();
        $session->setUtilisateur($user);
        $session->setDateDerniereActivite(new \DateTime()); // date_debut
        $session->setDateExpiration((new \DateTime())->modify(self::SESSION_DURATION)); // date_fin
        
        // Ajouter des informations supplémentaires si disponibles
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $session->setIpAddress($request->getClientIp());
            $session->setUserAgent($request->headers->get('User-Agent'));
        }
        
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        
        return [
            'token' => $session->getTokenSession(),
            'user' => $this->formatUserData($user)
        ];
    }
    
    /**
     * Vérifier si une session est valide
     */
    public function validateSession(string $token): ?Utilisateur
    {
        $session = $this->sessionRepository->findOneBy(['tokenSession' => $token]);
        
        if (!$session || $session->isExpired()) {
            return null;
        }
        
        // Mettre à jour la date de dernière activité
        $session->setDateDerniereActivite(new \DateTime());
        $this->entityManager->flush();
        
        return $session->getUtilisateur();
    }
    
    /**
     * Récupérer l'utilisateur à partir du cookie de session
     */
    public function getUserFromCookie(): ?Utilisateur
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return null;
        }
        
        $sessionToken = $request->cookies->get(self::SESSION_COOKIE_NAME);
        
        if (!$sessionToken) {
            return null;
        }
        
        return $this->validateSession($sessionToken);
    }
    
    /**
     * Détruire une session
     */
    public function logout(string $token): bool
    {
        $session = $this->sessionRepository->findOneBy(['tokenSession' => $token]);
        
        if (!$session) {
            return false;
        }
        
        $this->entityManager->remove($session);
        $this->entityManager->flush();
        
        return true;
    }
    
    /**
     * Formater les données utilisateur pour la réponse
     */
    private function formatUserData(Utilisateur $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'roles' => $user->getRoles(),
            'typeInterface' => $user->getTypeInterface(),
            'isAdmin' => $user->isAdministrateur(),
        ];
    }
    
    /**
     * Obtenir le nom du cookie de session
     */
    public function getSessionCookieName(): string
    {
        return self::SESSION_COOKIE_NAME;
    }
    
    /**
     * Nettoyer les sessions expirées
     */
    public function cleanExpiredSessions(): int
    {
        $expiredSessions = $this->sessionRepository->findExpiredSessions();
        $count = count($expiredSessions);
        
        foreach ($expiredSessions as $session) {
            $this->entityManager->remove($session);
        }
        
        $this->entityManager->flush();
        
        return $count;
    }
} 