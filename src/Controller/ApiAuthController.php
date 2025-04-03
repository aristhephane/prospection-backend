<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiAuthController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Endpoint d'authentification direct et explicite
     */
    #[Route('/api/auth/jwt-login', name: 'api_auth_direct_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email']) || !isset($data['password'])) {
                return new JsonResponse(['message' => 'Email et mot de passe requis'], Response::HTTP_BAD_REQUEST);
            }

            $email = $data['email'];
            $password = $data['password'];

            // Recherche de l'utilisateur par email
            $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            // Vérification si l'utilisateur existe et si le mot de passe correspond
            if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
                throw new BadCredentialsException('Identifiants invalides');
            }

            // Création du token JWT
            $token = $this->jwtManager->create($user);

            // Réponse avec le token et les informations de l'utilisateur
            return new JsonResponse([
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom()
                ]
            ]);
        } catch (BadCredentialsException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Une erreur est survenue: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint pour vérifier si un token est valide
     */
    #[Route('/api/auth/check', name: 'api_auth_check', methods: ['GET'])]
    public function checkToken(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['authenticated' => false], Response::HTTP_UNAUTHORIZED);
        }
        
        return new JsonResponse([
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
} 