<?php

// Script pour vérifier et déboger la structure de la table Session et la vérification du mot de passe

// Paramètres de connexion
$db_host = 'localhost';
$db_name = 'prospection_unified';
$db_user = 'projet';
$db_pass = 'Um6m9ooD';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connexion à la base de données réussie.\n\n";
    
    // 1. Vérifier la structure de la table
    echo "Structure de la table session:\n";
    $columns = $pdo->query("SHOW COLUMNS FROM session")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo " - {$column['Field']} ({$column['Type']})" . ($column['Null'] === 'NO' ? ' NOT NULL' : ' NULL') . "\n";
    }
    
    echo "\n";
    
    // 2. Vérifier si l'utilisateur admin existe
    $stmt = $pdo->prepare("SELECT id, email, actif, password FROM utilisateur WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "L'utilisateur admin@example.com n'existe pas!\n";
        exit(1);
    }
    
    echo "Utilisateur admin@example.com trouvé:\n";
    echo " - ID: {$user['id']}\n";
    echo " - Email: {$user['email']}\n";
    echo " - Actif: " . ($user['actif'] ? 'OUI' : 'NON') . "\n";
    echo " - Password: {$user['password']}\n\n";
    
    // 3. Vérifier le mot de passe
    $password = 'AdminPass123!';
    $isPasswordValid = password_verify($password, $user['password']);
    
    echo "Vérification du mot de passe 'AdminPass123!':\n";
    echo " - Résultat: " . ($isPasswordValid ? 'VALIDE' : 'INVALIDE') . "\n\n";
    
    // 4. Tester la création d'une session
    echo "Test de création d'une session:\n";
    
    // Vérifier si l'utilisateur a des sessions existantes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM session WHERE utilisateur_id = ?");
    $stmt->execute([$user['id']]);
    $sessionCount = $stmt->fetchColumn();
    
    echo " - Sessions existantes: $sessionCount\n";
    
    // Générer un token unique
    $token = bin2hex(random_bytes(32));
    $now = date('Y-m-d H:i:s');
    $expiration = date('Y-m-d H:i:s', strtotime('+8 hours'));
    
    // Insérer une nouvelle session
    try {
        $stmt = $pdo->prepare("INSERT INTO session (utilisateur_id, token_session, date_debut, date_fin, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $user['id'],
            $token,
            $now,
            $expiration,
            '127.0.0.1',
            'PHP Debug Script'
        ]);
        
        if ($result) {
            $sessionId = $pdo->lastInsertId();
            echo " - Nouvelle session créée avec succès (ID: $sessionId)\n";
            echo " - Token: $token\n";
            echo " - Date début: $now\n";
            echo " - Date fin: $expiration\n";
        } else {
            echo " - Échec de création de la session.\n";
        }
    } catch (PDOException $e) {
        echo " - Erreur lors de la création de la session: " . $e->getMessage() . "\n";
        
        // Afficher plus de détails sur l'erreur
        echo " - Erreur code: " . $e->getCode() . "\n";
        echo " - Infos supplémentaires:\n";
        print_r($e->errorInfo);
    }
    
} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
} 