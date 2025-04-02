<?php
// Script pour lister tous les utilisateurs dans la base de données
require_once __DIR__ . '/vendor/autoload.php';

// Récupérer les variables d'environnement depuis le fichier .env
$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Récupérer la chaîne de connexion DATABASE_URL
$databaseUrl = $_ENV['DATABASE_URL'];

try {
    // Connexion à la base de données
    $conn = \Doctrine\DBAL\DriverManager::getConnection(['url' => $databaseUrl]);
    
    // Requête pour lister tous les utilisateurs
    $sql = "SELECT u.id, u.email, u.roles, u.nom, u.prenom FROM user u";
    $stmt = $conn->prepare($sql);
    $users = $stmt->executeQuery()->fetchAllAssociative();
    
    // Afficher les utilisateurs
    echo "Liste des utilisateurs:\n";
    echo str_repeat('-', 100) . "\n";
    echo sprintf("%-5s | %-30s | %-30s | %-50s\n", "ID", "Email", "Nom", "Rôles");
    echo str_repeat('-', 100) . "\n";
    
    foreach ($users as $user) {
        $roles = is_string($user['roles']) ? json_decode($user['roles'], true) : $user['roles'];
        $rolesStr = is_array($roles) ? implode(', ', $roles) : $roles;
        echo sprintf("%-5s | %-30s | %-30s | %-50s\n", 
            $user['id'],
            $user['email'], 
            $user['nom'] . ' ' . $user['prenom'],
            $rolesStr
        );
    }
    
    // Requête pour lister les rôles disponibles
    $sql = "SELECT * FROM role";
    $stmt = $conn->prepare($sql);
    $roles = $stmt->executeQuery()->fetchAllAssociative();
    
    echo "\nRôles disponibles:\n";
    echo str_repeat('-', 50) . "\n";
    echo sprintf("%-5s | %-20s | %-50s\n", "ID", "Nom", "Description");
    echo str_repeat('-', 50) . "\n";
    
    foreach ($roles as $role) {
        echo sprintf("%-5s | %-20s | %-50s\n", 
            $role['id'],
            $role['nom'],
            $role['description']
        );
    }
    
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
} 