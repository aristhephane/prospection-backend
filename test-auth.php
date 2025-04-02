<?php
// test-auth.php
require_once __DIR__.'/vendor/autoload.php';

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Configuration manuelle
$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_DEBUG'] = '1';

// Charger le kernel
require_once __DIR__.'/src/Kernel.php';
$kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$entityManager = $container->get('doctrine.orm.entity_manager');
$passwordHasher = $container->get('security.user_password_hasher');

// Test de récupération d'un utilisateur
function testUserRetrieval($entityManager) {
    echo "Test de récupération d'un utilisateur:\n";
    
    $utilisateurRepository = $entityManager->getRepository(Utilisateur::class);
    $user = $utilisateurRepository->findOneBy(['email' => 'admin@upjv.fr']);
    
    if (!$user) {
        echo "❌ ERREUR: Utilisateur non trouvé!\n";
        return null;
    }
    
    echo "✅ Utilisateur trouvé: ID=" . $user->getId() . ", Email=" . $user->getEmail() . "\n";
    echo "   Nom complet: " . $user->getPrenom() . " " . $user->getNom() . "\n";
    
    // Afficher les rôles
    $roles = $user->getRoles();
    echo "   Rôles: " . implode(', ', $roles) . "\n";
    
    // Afficher le hash du mot de passe
    echo "   Hash du mot de passe: " . $user->getPassword() . "\n";
    
    return $user;
}

// Test de vérification du mot de passe
function testPasswordVerification($passwordHasher, $user, $password) {
    echo "\nTest de vérification du mot de passe:\n";
    
    if (!$user) {
        echo "❌ ERREUR: Impossible de tester le mot de passe (utilisateur non disponible)\n";
        return false;
    }
    
    $isValid = $passwordHasher->isPasswordValid($user, $password);
    
    if ($isValid) {
        echo "✅ Mot de passe valide pour l'utilisateur " . $user->getEmail() . "\n";
    } else {
        echo "❌ ERREUR: Mot de passe invalide pour l'utilisateur " . $user->getEmail() . "\n";
        
        // Vérifier le format du mot de passe hashé
        echo "   Format du hash stocké: " . $user->getPassword() . "\n";
        
        // Générer un nouveau hash pour comparaison
        $newHash = $passwordHasher->hashPassword($user, $password);
        echo "   Nouveau hash généré: " . $newHash . "\n";
    }
    
    return $isValid;
}

// Test de la configuration JWT
function testJwtConfig($kernel) {
    echo "\nTest de la configuration JWT:\n";
    
    $projectDir = $kernel->getProjectDir();
    $privateKeyPath = $projectDir . '/config/jwt/private.pem';
    $publicKeyPath = $projectDir . '/config/jwt/public.pem';
    
    $privateKeyExists = file_exists($privateKeyPath);
    $publicKeyExists = file_exists($publicKeyPath);
    
    if ($privateKeyExists && $publicKeyExists) {
        echo "✅ Les clés JWT existent\n";
        echo "   Clé privée: " . $privateKeyPath . " (" . substr(sprintf('%o', fileperms($privateKeyPath)), -4) . ")\n";
        echo "   Clé publique: " . $publicKeyPath . " (" . substr(sprintf('%o', fileperms($publicKeyPath)), -4) . ")\n";
        
        // Vérifier la configuration dans .env
        $envFile = file_get_contents($projectDir . '/.env');
        if (strpos($envFile, 'JWT_SECRET_KEY') !== false && 
            strpos($envFile, 'JWT_PUBLIC_KEY') !== false && 
            strpos($envFile, 'JWT_PASSPHRASE') !== false) {
            echo "✅ Variables JWT configurées dans .env\n";
        } else {
            echo "❌ ERREUR: Variables JWT manquantes dans .env\n";
        }
    } else {
        echo "❌ ERREUR: Les clés JWT n'existent pas!\n";
        if (!$privateKeyExists) echo "   Clé privée manquante: " . $privateKeyPath . "\n";
        if (!$publicKeyExists) echo "   Clé publique manquante: " . $publicKeyPath . "\n";
    }
}

// Exécution des tests
echo "=== TESTS D'AUTHENTIFICATION ===\n\n";

$user = testUserRetrieval($entityManager);
$passwordValid = testPasswordVerification($passwordHasher, $user, 'Admin@2025');
testJwtConfig($kernel);

// Test avec un nouveau mot de passe hashé manuellement
if ($user && !$passwordValid) {
    echo "\nTest avec génération manuelle d'un nouvel utilisateur:\n";
    
    // Créer un nouvel utilisateur pour tester le hashage
    $newUser = new Utilisateur();
    $newUser->setEmail('test@example.com');
    $newUser->setNom('Test');
    $newUser->setPrenom('User');
    $newUser->setDateCreation(new \DateTime());
    $newUser->setActif(true);
    
    $hashedPassword = $passwordHasher->hashPassword($newUser, 'Admin@2025');
    $newUser->setPassword($hashedPassword);
    
    echo "   Nouveau hash généré pour 'Admin@2025': " . $hashedPassword . "\n";
    echo "   Vérification du nouveau hash: " . ($passwordHasher->isPasswordValid($newUser, 'Admin@2025') ? "Valide" : "Invalide") . "\n";
}

echo "\n=== RÉSUMÉ DES TESTS ===\n";
echo ($user ? "✅" : "❌") . " Récupération de l'utilisateur\n";
echo ($passwordValid ? "✅" : "❌") . " Vérification du mot de passe\n";

echo "\nTests terminés.\n"; 