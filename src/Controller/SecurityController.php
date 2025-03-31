<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    private $jwtManager;
    private $tokenStorage;
    private $entityManager;
    private $passwordHasher;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Affiche la page de connexion.
     */
    #[Route('/login', name: 'security_login', methods: ['GET'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            // Utilisation de l'utilitaire personnalisé pour obtenir une URL valide
            $redirectUrl = $this->generateUrl('home');
            return $this->redirect($redirectUrl);
        }

        // Récupération des erreurs d'authentification et du dernier nom d'utilisateur
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/api/token/authenticate', name: 'api_token_authenticate', methods: ['POST'])]
    public function apiTokenAuthenticate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Email et mot de passe requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                return $this->json([
                    'message' => 'Identifiants invalides'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Générer le token JWT
            $token = $this->jwtManager->create($user);

            return $this->json([
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom()
                ]
            ]);
        } catch (\Exception $e) {
            error_log('Erreur authentification: ' . $e->getMessage());
            return $this->json([
                'message' => 'Une erreur est survenue lors de l\'authentification: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * La déconnexion est gérée par Symfony.
     * Cette méthode ne sera jamais exécutée, elle est interceptée par le firewall.
     */
    #[Route('/logout', name: 'security_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Le contrôleur logout peut rester vide : la gestion se fait via la configuration du firewall.
        throw new \Exception('Cette méthode ne doit pas être appelée directement.');
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function apiLogout(): JsonResponse
    {
        return $this->json(['message' => 'Logged out successfully']);
    }

    #[Route('/api/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            // Récupérer l'utilisateur courant
            $user = $this->getUser();
            
            if (!$user) {
                return $this->json([
                    'error' => 'Utilisateur non authentifié',
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Générer un nouveau token
            $token = $this->jwtManager->create($user);
            
            // Générer un refresh token (dans un cas réel, implémenter un mécanisme plus sécurisé)
            $refreshToken = hash('sha256', random_bytes(32) . $user->getUserIdentifier() . time());
            
            return $this->json([
                'token' => $token,
                'refresh_token' => $refreshToken,
                'user' => [
                    'identifier' => $user->getUserIdentifier(),
                    'roles' => $user->getRoles()
                ]
            ]);
        } catch (\Exception $e) {
            error_log('Token refresh error: ' . $e->getMessage());
            return $this->json([
                'error' => 'Refresh token error',
                'message' => 'Une erreur est survenue lors du rafraîchissement du token'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/auth-test', name: 'api_auth_test', methods: ['GET'])]
    public function apiAuthTest(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'message' => 'Authentification requise'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'message' => 'Authentification réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom()
            ]
        ]);
    }

    #[Route('/api/auth-status', name: 'api_auth_status', methods: ['GET'])]
    public function apiAuthStatus(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'authenticated' => false,
                'message' => 'Non authentifié'
            ]);
        }

        return $this->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom()
            ]
        ]);
    }

    #[Route('/api/debug-jwt', name: 'api_debug_jwt')]
    public function debugJwt(): JsonResponse
    {
        // Désactiver en production
        if ($this->getParameter('kernel.environment') === 'prod') {
            return $this->json([
                'error' => 'Cette fonctionnalité est désactivée en production'
            ], Response::HTTP_FORBIDDEN);
        }
        
        try {
            // Vérifier les clés JWT
            $projectDir = $this->getParameter('kernel.project_dir');
            $privateKeyPath = $projectDir . '/config/jwt/private.pem';
            $publicKeyPath = $projectDir . '/config/jwt/public.pem';

            $privateKeyExists = file_exists($privateKeyPath);
            $publicKeyExists = file_exists($publicKeyPath);

            // Vérifier les permissions
            $privateKeyPerms = $privateKeyExists ? substr(sprintf('%o', fileperms($privateKeyPath)), -4) : 'N/A';
            $publicKeyPerms = $publicKeyExists ? substr(sprintf('%o', fileperms($publicKeyPath)), -4) : 'N/A';

            return $this->json([
                'jwt_config' => [
                    'private_key_exists' => $privateKeyExists,
                    'public_key_exists' => $publicKeyExists,
                    'private_key_perms' => $privateKeyPerms,
                    'public_key_perms' => $publicKeyPerms,
                ],
                'jwt_dir_exists' => is_dir($projectDir . '/config/jwt'),
                'jwt_dir_perms' => is_dir($projectDir . '/config/jwt') ?
                    substr(sprintf('%o', fileperms($projectDir . '/config/jwt')), -4) : 'N/A',
            ]);
        } catch (\Exception $e) {
            error_log('JWT debug error: ' . $e->getMessage());
            return $this->json([
                'error' => 'JWT debug failed',
                'message' => 'Une erreur est survenue lors du débogage JWT'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/auth/token', name: 'api_auth_token', methods: ['POST'])]
    public function apiAuthToken(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Email et mot de passe requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                return $this->json([
                    'message' => 'Identifiants invalides'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Générer le token JWT
            $token = $this->jwtManager->create($user);

            return $this->json([
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom()
                ]
            ]);
        } catch (\Exception $e) {
            error_log('Erreur authentification: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return $this->json([
                'message' => 'Une erreur est survenue lors de l\'authentification: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
