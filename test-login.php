<?php
// Script de test pour l'authentification API
$url = 'https://upjv-prospection-vps.amourfoot.fr/api/api/login_check';

// Données à envoyer
$data = [
    'username' => 'superadmin@upjv.fr',
    'password' => 'admin123'
];

// Configuration de la requête cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

// Exécution de la requête
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Affichage des résultats
echo "Code HTTP: $httpCode\n";
if ($error) {
    echo "Erreur cURL: $error\n";
}
echo "Réponse:\n";
echo $response ? json_encode(json_decode($response), JSON_PRETTY_PRINT) : "Aucune réponse\n";

// Tester également l'utilisateur admin@example.com
$data = [
    'username' => 'admin@example.com',
    'password' => 'Admin123!'
];

// Configuration de la requête cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

// Exécution de la requête
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Affichage des résultats
echo "\n\n=== Test avec admin@example.com ===\n";
echo "Code HTTP: $httpCode\n";
if ($error) {
    echo "Erreur cURL: $error\n";
}
echo "Réponse:\n";
echo $response ? json_encode(json_decode($response), JSON_PRETTY_PRINT) : "Aucune réponse\n"; 