<?php
// Script pour tester l'authentification directement avec les utilisateurs en base
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

// Chargement des variables d'environnement
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Configuration de la connexion à la base de données
$dsn = $_ENV['DATABASE_URL'];

try {
    // Connexion à la base de données
    $conn = \Doctrine\DBAL\DriverManager::getConnection(['url' => $dsn]);
    
    // Récupérer la liste des utilisateurs
    $sql = "SELECT id, email, password, nom, prenom FROM utilisateur";
    $stmt = $conn->prepare($sql);
    $users = $stmt->executeQuery()->fetchAllAssociative();
    
    echo "Liste des utilisateurs:\n";
    echo str_repeat('-', 100) . "\n";
    
    // Factory pour la vérification des mots de passe
    $factory = new PasswordHasherFactory([
        'common' => ['algorithm' => 'auto']
    ]);
    $hasher = $factory->getPasswordHasher('common');
    
    // Tester les mots de passe pour chaque utilisateur
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Email: {$user['email']}, Nom: {$user['nom']} {$user['prenom']}\n";
        
        // Tester les mots de passe
        $passwords = [
            'Admin@2025', 
            'Director@2025', 
            'Prospect@2025', 
            'Teach@2025',
            'admin123',
            'Admin123!'
        ];
        
        foreach ($passwords as $password) {
            $isValid = password_verify($password, $user['password']);
            echo "  Mot de passe '$password': " . ($isValid ? 'VALIDE ✅' : 'invalide ❌') . "\n";
        }
        
        echo str_repeat('-', 100) . "\n";
    }

    // Tester l'authentification via curl
    echo "\nTest d'authentification via API:\n";
    echo str_repeat('-', 100) . "\n";
    
    $api_url = 'https://upjv-prospection-vps.amourfoot.fr/api/api/login_check';
    
    // Tester avec chaque utilisateur et mot de passe
    foreach ($users as $user) {
        foreach ($passwords as $password) {
            $data = [
                'username' => $user['email'],
                'password' => $password
            ];
            
            // Test de l'API
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "API Test: {$user['email']} / $password -> Code: $httpCode\n";
            if ($httpCode === 200) {
                echo "  SUCCÈS: " . substr($response, 0, 100) . "...\n";
                echo str_repeat('-', 100) . "\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
} 