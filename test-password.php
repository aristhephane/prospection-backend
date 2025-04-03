<?php
// Script pour tester si le mot de passe est valide

// Le mot de passe à tester
$password = 'AdminPass123!';

// Le hash stocké dans la base de données
$hash = '$2y$13$1IgGSN8pFI94PsX.qfjqvOW3DfHgXDGah0T3cO1IMSBxI7FNwmPEq';

// Vérifier si le mot de passe correspond au hash
$isValid = password_verify($password, $hash);

echo "Mot de passe: $password\n";
echo "Hash: $hash\n";
echo "Résultat: " . ($isValid ? "VALIDE" : "INVALIDE") . "\n";

// Générer un nouveau hash pour comparaison
echo "\nGénération d'un nouveau hash pour le même mot de passe:\n";
$newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);
echo "Nouveau hash: $newHash\n";
echo "Vérification du nouveau hash: " . (password_verify($password, $newHash) ? "VALIDE" : "INVALIDE") . "\n";

// Vérifier l'algorithme utilisé dans le hash original
echo "\nInformation sur le hash original:\n";
$info = password_get_info($hash);
echo "Algorithme: " . $info['algoName'] . "\n";
echo "Options:\n";
print_r($info['options']); 