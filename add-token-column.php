<?php

// Script pour ajouter la colonne token_session à la table session

// Paramètres de connexion à la base de données
$db_host = 'localhost';
$db_name = 'prospection_unified';
$db_user = 'projet';
$db_pass = 'Um6m9ooD';

try {
    // Connexion à la BDD
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à la base de données réussie.\n";
    
    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM session LIKE 'token_session'");
    $columnExists = $stmt->fetchColumn();
    
    if ($columnExists) {
        echo "La colonne 'token_session' existe déjà dans la table session.\n";
    } else {
        // Ajouter la colonne
        $pdo->exec("ALTER TABLE session ADD COLUMN token_session VARCHAR(255) NULL AFTER utilisateur_id");
        echo "La colonne 'token_session' a été ajoutée à la table session avec succès.\n";
    }
    
    echo "Opération terminée.\n";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
} 