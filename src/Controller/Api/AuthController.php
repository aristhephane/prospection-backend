<?php

namespace App\Controller\Api;

use App\Service\SessionAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly SessionAuthService $sessionAuthService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Endpoint d'authentification - création d'une session
     */
    #[Route('/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'message' => 'Email et mot de passe requis',
                'error' => 'Email et mot de passe requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        // Mode debug: vérification directe
        $debugInfo = [];
        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        
        if (!$user) {
            $debugInfo['user_found'] = false;
            return $this->json([
                'message' => 'Utilisateur introuvable',
                'error' => 'Invalid credentials.',
                'debug' => $debugInfo
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        $debugInfo['user_found'] = true;
        $debugInfo['user_id'] = $user->getId();
        $debugInfo['user_active'] = $user->isActif();
        $debugInfo['password_valid'] = $this->passwordHasher->isPasswordValid($user, $password);
        
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'message' => 'Mot de passe invalide',
                'error' => 'Invalid credentials.',
                'debug' => $debugInfo
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Authentifier l'utilisateur
        $result = $this->sessionAuthService->login($email, $password);

        if (!$result) {
            $debugInfo['service_login_success'] = false;
            return $this->json([
                'message' => 'Échec de création de session',
                'error' => 'Invalid credentials.',
                'debug' => $debugInfo
            ], Response::HTTP_UNAUTHORIZED);
        }

        $debugInfo['service_login_success'] = true;
        $debugInfo['token_generated'] = true;
        
        // Créer un cookie de session
        $response = $this->json([
            'message' => 'Authentification réussie',
            'user' => $result['user'],
            'debug' => $debugInfo
        ]);

        // Définir le cookie de session
        $cookie = Cookie::create($this->sessionAuthService->getSessionCookieName())
            ->withValue($result['token'])
            ->withExpires(new \DateTime('+8 hours'))
            ->withPath('/')
            ->withHttpOnly(true)
            ->withSecure(true)
            ->withSameSite('strict');

        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Vérifier l'état d'authentification
     */
    #[Route('/auth/status', name: 'api_auth_status', methods: ['GET'])]
    public function status(#[CurrentUser] ?Utilisateur $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['authenticated' => false], Response::HTTP_OK);
        }

        return $this->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'roles' => $user->getRoles(),
                'typeInterface' => $user->getTypeInterface(),
                'isAdmin' => $user->isAdministrateur(),
            ]
        ]);
    }

    /**
     * Déconnexion - suppression de la session
     */
    #[Route('/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $sessionToken = $request->cookies->get($this->sessionAuthService->getSessionCookieName());

        if ($sessionToken) {
            $this->sessionAuthService->logout($sessionToken);
        }

        $response = $this->json(['message' => 'Déconnexion réussie']);

        // Supprimer le cookie de session
        $response->headers->clearCookie(
            $this->sessionAuthService->getSessionCookieName(),
            '/',
            null,
            true,
            true,
            'strict'
        );

        return $response;
    }
} 