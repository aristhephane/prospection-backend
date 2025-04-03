<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class LoginAliasController extends AbstractController
{
  private $jwtManager;
  private $entityManager;
  private $passwordHasher;

  public function __construct(
    JWTTokenManagerInterface $jwtManager,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
  ) {
    $this->jwtManager = $jwtManager;
    $this->entityManager = $entityManager;
    $this->passwordHasher = $passwordHasher;
  }

  #[Route('/login', name: 'api_login_alias', methods: ['POST'])]
  public function login(Request $request): Response
  {
    // Vérifier si la requête attend une réponse JSON (API) ou HTML (navigateur)
    $expectsJson = $request->headers->get('Accept') === 'application/json'
      || $request->headers->get('Content-Type') === 'application/json'
      || $request->query->get('format') === 'json';

    try {
      // Si la requête est au format JSON (API)
      if ($expectsJson) {
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données nécessaires sont présentes
        if (
          !$data ||
          (!isset($data['username']) && !isset($data['email'])) ||
          !isset($data['password'])
        ) {
          return $this->json([
            'message' => 'Username/email et mot de passe requis'
          ], Response::HTTP_BAD_REQUEST);
        }

        // Accepter soit username soit email
        $email = $data['email'] ?? $data['username'];

        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

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
      }
      // Si la requête vient d'un formulaire HTML (navigateur)
      else {
        // Récupérer les données du formulaire
        $email = $request->request->get('_username');
        $password = $request->request->get('_password');

        if (!$email || !$password) {
          // Rediriger vers la page de connexion avec un message d'erreur
          return $this->redirectToRoute('security_login', [
            'error' => 'Veuillez fournir un email et un mot de passe'
          ]);
        }

        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
          // Rediriger vers la page de connexion avec un message d'erreur
          return $this->redirectToRoute('security_login', [
            'error' => 'Identifiants invalides'
          ]);
        }

        // Rediriger vers le tableau de bord après connexion réussie
        return $this->redirectToRoute('dashboard_index');
      }
    } catch (\Exception $e) {
      error_log('Erreur authentification: ' . $e->getMessage());

      if ($expectsJson) {
        return $this->json([
          'message' => 'Une erreur est survenue lors de l\'authentification: ' . $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
      } else {
        return $this->redirectToRoute('security_login', [
          'error' => 'Une erreur est survenue lors de la connexion'
        ]);
      }
    }
  }
}
