<?php
// Script pour tester l'authentification directement avec les utilisateurs en base
require dirname(__DIR__).'/prospection-backend/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();

// Créer une requête simulée
$content = json_encode([
    'username' => 'admin@upjv.fr',
    'password' => 'Admin@2025'
]);

$request = Request::create(
    '/api/login_check',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json'],
    $content
);

// Exécuter la requête
try {
    $response = $kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, true);
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response Headers:\n";
    foreach ($response->headers->all() as $name => $values) {
        echo $name . ": " . implode(", ", $values) . "\n";
    }
    echo "\nResponse Content:\n" . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} 