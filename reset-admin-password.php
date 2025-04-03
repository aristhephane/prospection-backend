<?php
// Script pour réinitialiser le mot de passe de l'administrateur

// Paramètres de connexion
$db_host = 'localhost';
$db_name = 'prospection_unified';
$db_user = 'projet';
$db_pass = 'Um6m9ooD';

// Nouveau mot de passe
$new_password = 'AdminPass123!';
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 15]);

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connexion à la base de données réussie.\n";
    
    // Mise à jour du mot de passe pour admin@example.com
    $stmt = $pdo->prepare("UPDATE utilisateur SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'admin@example.com']);
    
    echo "Mot de passe de admin@example.com mis à jour. Nombre de lignes affectées: " . $stmt->rowCount() . "\n";
    
    // Mise à jour du mot de passe pour admin@upjv.fr
    $stmt = $pdo->prepare("UPDATE utilisateur SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'admin@upjv.fr']);
    
    echo "Mot de passe de admin@upjv.fr mis à jour. Nombre de lignes affectées: " . $stmt->rowCount() . "\n";
    
    echo "Nouveau mot de passe: $new_password\n";
    echo "Hash du nouveau mot de passe: $hashed_password\n";
    
    // Vérification
    $stmt = $pdo->prepare("SELECT id, email, password FROM utilisateur WHERE email IN (?, ?)");
    $stmt->execute(['admin@example.com', 'admin@upjv.fr']);
    
    echo "\nUtilisateurs mis à jour:\n";
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: {$user['id']}, Email: {$user['email']}\n";
        echo "  Password hash: {$user['password']}\n";
        echo "  Vérification: " . (password_verify($new_password, $user['password']) ? "VALIDE" : "INVALIDE") . "\n\n";
    }
    
    echo "Réinitialisation du mot de passe terminée avec succès.\n";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
    exit(1);
} 