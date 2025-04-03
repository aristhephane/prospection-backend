<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/api')]
class DiagnosticController extends AbstractController
{
  #[Route('/server-info', name: 'api_server_info', methods: ['GET'])]
  public function serverInfo(ParameterBagInterface $params): JsonResponse
  {
    // Informations de base non sensibles
    return $this->json([
      'php_version' => PHP_VERSION,
      'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
      'environment' => $params->get('kernel.environment'),
      'debug' => $params->get('kernel.debug'),
      'project_dir' => $params->get('kernel.project_dir'),
      'charset' => $params->get('kernel.charset')
    ]);
  }

  #[Route('/cors-test', name: 'api_cors_test', methods: ['GET', 'OPTIONS', 'POST'])]
  public function corsTest(): JsonResponse
  {
    return $this->json([
      'message' => 'CORS test successful',
      'headers_received' => getallheaders(),
      'method' => $_SERVER['REQUEST_METHOD']
    ]);
  }

  #[Route('/auth-diagnostic', name: 'api_auth_diagnostic', methods: ['POST'])]
  public function authDiagnostic(): JsonResponse
  {
    // Ce endpoint est uniquement pour tester les en-têtes d'authentification
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? null;

    return $this->json([
      'message' => 'Diagnostic d\'authentification',
      'auth_header_present' => $authHeader !== null,
      'auth_header_type' => $authHeader ? substr($authHeader, 0, 7) : 'Not provided',
    ]);
  }
}
