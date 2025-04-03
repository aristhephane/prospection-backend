<?php
/**
 * Script pont d'authentification autonome compatible Symfony 7.2
 * 
 * Ce script contourne le système de routage de Symfony tout en utilisant
 * les services de l'application pour l'authentification.
 */

// Éviter les problèmes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

// Si la méthode est OPTIONS, répondre immédiatement avec un succès (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit(1);
}

// Récupérer le contenu JSON de la requête
$requestData = json_decode(file_get_contents('php://input'), true);

if (!isset($requestData['email']) || !isset($requestData['password'])) {
    echo json_encode(['error' => 'Email et mot de passe requis']);
    http_response_code(400);
    exit(1);
}

// Initialisation de l'environnement Symfony 7.2
$projectDir = dirname(__DIR__);
require_once $projectDir.'/vendor/autoload.php';

use App\Entity\Utilisateur;
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
(new Dotenv())->bootEnv($projectDir.'/.env');

// Création du kernel et du conteneur selon la méthode Symfony 7.2
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

// Récupérer les services nécessaires - utilisation de l'ID des services au lieu des classes
try {
    $entityManager = $container->get('doctrine.orm.entity_manager');
    $passwordHasher = $container->get('security.user_password_hasher');
    $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');

    // Rechercher l'utilisateur par email
    $userRepository = $entityManager->getRepository(Utilisateur::class);
    $user = $userRepository->findOneBy(['email' => $requestData['email']]);
    
    // Vérifier si l'utilisateur existe et si le mot de passe correspond
    if (!$user) {
        throw new \Exception('Utilisateur non trouvé');
    }
    
    if (!$passwordHasher->isPasswordValid($user, $requestData['password'])) {
        throw new \Exception('Mot de passe invalide');
    }
    
    // Générer le token JWT
    $token = $jwtManager->create($user);
    
    // Retourner la réponse avec le token et les informations de l'utilisateur
    echo json_encode([
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
    http_response_code(401);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => 401
    ]);
}

// Nettoyage des ressources
$kernel->terminate(Request::createFromGlobals(), null);
