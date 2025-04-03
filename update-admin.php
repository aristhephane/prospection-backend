<?php
// Connexion directe à la base de données pour mettre à jour le mot de passe de l'admin

// Récupération du hash créé précédemment
$newPasswordHash = '$2y$13$1IgGSN8pFI94PsX.qfjqvOW3DfHgXDGah0T3cO1IMSBxI7FNwmPEq';

// Paramètres de connexion à la base de données (tirés de .env)
$dsn = 'mysql:host=127.0.0.1;dbname=prospection_unified;charset=utf8mb4';
$user = 'projet';
$password = 'Um6m9ooD';

try {
    // Connexion à la base de données
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Mettre à jour le mot de passe de l'admin
    $stmt = $pdo->prepare('UPDATE utilisateur SET password = :password WHERE email = :email');
    $result = $stmt->execute([
        'password' => $newPasswordHash,
        'email' => 'admin@example.com'
    ]);

    if ($result) {
        echo "Le mot de passe de l'utilisateur admin@example.com a été mis à jour avec succès.\n";
        echo "Nouveau hash: $newPasswordHash\n";
    } else {
        echo "Échec de la mise à jour du mot de passe.\n";
    }

    // Vérification que l'utilisateur existe toujours
    $stmt = $pdo->prepare('SELECT id, email, password FROM utilisateur WHERE email = :email');
    $stmt->execute(['email' => 'admin@example.com']);
    $admin = $stmt->fetch();

    if ($admin) {
        echo "Vérification réussie: l'utilisateur admin@example.com existe dans la base de données.\n";
        echo "ID: " . $admin['id'] . "\n";
        echo "Hash stocké: " . substr($admin['password'], 0, 30) . "...\n";
    } else {
        echo "ERREUR: L'utilisateur admin@example.com n'a pas été trouvé dans la base de données.\n";
    }
} catch (PDOException $e) {
    echo "Erreur de base de données: " . $e->getMessage() . "\n";
    exit(1);
} 