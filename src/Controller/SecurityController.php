<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use App\Security\AuthenticationUtils as CustomAuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Affiche la page de connexion.
     */
    #[Route('/login', name: 'security_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, CustomAuthenticationUtils $customAuthUtils): Response
    {
        // Vérifie si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            // Utilisation de l'utilitaire personnalisé pour obtenir une URL valide
            $redirectUrl = $customAuthUtils->getRedirectUrl();
            return $this->redirect($redirectUrl);
        }

        // Récupération des erreurs d'authentification et du dernier nom d'utilisateur
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'redirect_url' => $customAuthUtils->getRedirectUrl(),
        ]);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(Request $request): JsonResponse
    {
        try {
            // Cette méthode ne devrait jamais être exécutée directement
            // car le bundle JWT intercepte la requête avant
            $user = $this->getUser();

            if (!$user) {
                return $this->json([
                    'error' => 'Invalid credentials',
                    'message' => 'Authentication failed. User not found.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->json([
                'user' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
                'message' => 'This endpoint was reached directly, which should not happen with JWT authentication.'
            ]);
        } catch (\Exception $e) {
            // Log détaillé de l'erreur
            error_log('API Login Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return $this->json([
                'error' => 'Authentication error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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

    #[Route('/api/logout', name: 'app_logout')]
    public function apiLogout(): void
    {
        // This method can be empty, as it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method should not be reached.');
    }

    #[Route('/api/auth-test', name: 'api_auth_test', methods: ['POST', 'GET'])]
    public function apiAuthTest(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];

            // Vérifier si les clés JWT existent
            $privateKeyPath = $this->getParameter('kernel.project_dir') . '/config/jwt/private.pem';
            $publicKeyPath = $this->getParameter('kernel.project_dir') . '/config/jwt/public.pem';

            $jwtKeysExist = file_exists($privateKeyPath) && file_exists($publicKeyPath);

            return $this->json([
                'received' => $data,
                'request_method' => $request->getMethod(),
                'auth_header' => $request->headers->get('Authorization'),
                'content_type' => $request->headers->get('Content-Type'),
                'jwt_keys_exist' => $jwtKeysExist,
                'jwt_paths' => [
                    'private' => $privateKeyPath,
                    'public' => $publicKeyPath,
                ],
                'server_info' => [
                    'PHP_VERSION' => PHP_VERSION,
                    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
                    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
                    'APP_ENV' => $_SERVER['APP_ENV'] ?? getenv('APP_ENV'),
                ],
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Test failed',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/auth-status', name: 'api_auth_status')]
    public function apiAuthStatus(): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json([
                    'authenticated' => false,
                    'message' => 'No authenticated user found'
                ]);
            }

            return $this->json([
                'authenticated' => true,
                'user' => [
                    'identifier' => $user->getUserIdentifier(),
                    'roles' => $user->getRoles()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Status check failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/debug-jwt', name: 'api_debug_jwt')]
    public function debugJwt(): JsonResponse
    {
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

            // Vérifier les propriétaires
            $privateKeyOwner = $privateKeyExists ? (function_exists('fileowner') ? fileowner($privateKeyPath) : 'unknown') : 'N/A';
            $publicKeyOwner = $publicKeyExists && function_exists('posix_getpwuid')
                ? posix_getpwuid(fileowner($publicKeyPath))['name']
                : 'unknown';

            return $this->json([
                'jwt_config' => [
                    'private_key_path' => $privateKeyPath,
                    'public_key_path' => $publicKeyPath,
                    'private_key_exists' => $privateKeyExists,
                    'public_key_exists' => $publicKeyExists,
                    'private_key_perms' => $privateKeyPerms,
                    'public_key_perms' => $publicKeyPerms,
                    'private_key_owner' => $privateKeyOwner,
                    'public_key_owner' => $publicKeyOwner,
                ],
                'jwt_dir_exists' => is_dir($projectDir . '/config/jwt'),
                'jwt_dir_perms' => is_dir($projectDir . '/config/jwt') ?
                    substr(sprintf('%o', fileperms($projectDir . '/config/jwt')), -4) : 'N/A',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'JWT debug failed',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
