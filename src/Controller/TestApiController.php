<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TestApiController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;
    private $tokenStorage;
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    #[Route('/api/test-auth', name: 'api_test_auth', methods: ['POST'])]
    public function testAuth(Request $request): JsonResponse
    {
        try {
            // Récupérer les données de la requête
            $data = json_decode($request->getContent(), true);

            if (!isset($data['username']) || !isset($data['password'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Username et password sont requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Rechercher l'utilisateur par email
            $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $data['username']]);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé',
                    'email_recherché' => $data['username']
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Vérifier le mot de passe
            $passwordValid = $this->passwordHasher->isPasswordValid($user, $data['password']);

            if (!$passwordValid) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Mot de passe invalide',
                    'email' => $user->getEmail(),
                    'password_length' => strlen($data['password']),
                    'password_hash_type' => substr($user->getPassword(), 0, 4)
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Générer un token JWT
            $token = $this->jwtManager->create($user);

            // Récupérer les informations de l'utilisateur
            $userData = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'roles' => $user->getRoles(),
                'typeInterface' => $user->getTypeInterface()
            ];

            return $this->json([
                'status' => 'success',
                'token' => $token,
                'user' => $userData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/test-info', name: 'api_test_info', methods: ['GET'])]
    public function testInfo(): JsonResponse
    {
        try {
            // Récupérer la liste des utilisateurs et compter
            $utilisateurs = $this->entityManager->getRepository(Utilisateur::class)->findAll();
            $userCount = count($utilisateurs);
            
            // Configuration JWT
            $projectDir = $this->getParameter('kernel.project_dir');
            $privateKeyPath = $projectDir . '/config/jwt/private.pem';
            $publicKeyPath = $projectDir . '/config/jwt/public.pem';
            
            $privateKeyExists = file_exists($privateKeyPath);
            $publicKeyExists = file_exists($publicKeyPath);
            
            $privateKeyPerms = $privateKeyExists ? substr(sprintf('%o', fileperms($privateKeyPath)), -4) : 'N/A';
            $publicKeyPerms = $publicKeyExists ? substr(sprintf('%o', fileperms($publicKeyPath)), -4) : 'N/A';
            
            return $this->json([
                'status' => 'success',
                'environment' => $this->getParameter('kernel.environment'),
                'debug' => $this->getParameter('kernel.debug'),
                'user_count' => $userCount,
                'users' => array_map(function($user) {
                    return [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'roles' => $user->getRoles()
                    ];
                }, $utilisateurs),
                'jwt_config' => [
                    'private_key' => [
                        'exists' => $privateKeyExists,
                        'path' => $privateKeyPath,
                        'permissions' => $privateKeyPerms
                    ],
                    'public_key' => [
                        'exists' => $publicKeyExists,
                        'path' => $publicKeyPath,
                        'permissions' => $publicKeyPerms
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 