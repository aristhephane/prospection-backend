<?php

// Paramètres d'authentification
$email = 'admin@example.com';
$password = 'AdminPass123!';
$storedHash = '$2y$13$1IgGSN8pFI94PsX.qfjqvOW3DfHgXDGah0T3cO1IMSBxI7FNwmPEq';

// Vérifier le mot de passe avec password_verify
$isValid = password_verify($password, $storedHash);

echo "Email: $email\n";
echo "Mot de passe: $password\n";
echo "Hash stocké: $storedHash\n";
echo "Résultat de la vérification: " . ($isValid ? "VALIDE" : "INVALIDE") . "\n";

// Créer un nouveau hash pour comparer
$newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);
$isValidNewHash = password_verify($password, $newHash);

echo "\nNouveau hash: $newHash\n";
echo "Vérification du nouveau hash: " . ($isValidNewHash ? "VALIDE" : "INVALIDE") . "\n"; 