<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('')]
class SecurityController extends AbstractController
{
    private $jwtManager;
    private $tokenStorage;
    private $serializer;
    private $entityManager;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ) {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    #[Route('/auth-test', name: 'api_auth_test', methods: ['GET'])]
    public function testAuthentication(): JsonResponse
    {
        return new JsonResponse(['status' => 'API is working'], Response::HTTP_OK);
    }

    #[Route('/auth-status', name: 'api_auth_status', methods: ['GET'])]
    public function getUserStatus(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof Utilisateur) {
            return new JsonResponse(['authenticated' => false], Response::HTTP_OK);
        }

        // Obtenir tous les rôles de l'utilisateur
        $roles = $user->getRoles();
        
        // Déterminer le type d'interface (administrateur ou utilisateur)
        $typeInterface = $user->getTypeInterface();

        // Créer un tableau avec les informations essentielles de l'utilisateur
        $userData = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'roles' => $roles,
            'typeInterface' => $typeInterface,
            'isAdmin' => $user->isAdministrateur(),
        ];

        return new JsonResponse([
            'authenticated' => true,
            'user' => $userData
        ], Response::HTTP_OK);
    }

    #[Route('/notifications/count', name: 'api_notifications_count', methods: ['GET'])]
    public function getNotificationsCount(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Authentification requise'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Compte les notifications non lues pour l'utilisateur
        $count = $this->entityManager->getRepository(\App\Entity\Notification::class)
            ->count(['utilisateur' => $user, 'lu' => false]);
        
        return new JsonResponse(['count' => $count], Response::HTTP_OK);
    }
} 