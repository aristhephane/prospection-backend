<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JwtAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  private $jwtManager;

  public function __construct(JWTTokenManagerInterface $jwtManager)
  {
    $this->jwtManager = $jwtManager;
  }

  /**
   * Crée une réponse personnalisée en cas d'authentification réussie.
   * Version simplifiée qui ne fait aucune redirection.
   * 
   * @param Request $request
   * @param TokenInterface $token
   * 
   * @return JsonResponse
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
  {
    $user = $token->getUser();

    if (!$user instanceof UserInterface) {
      return new JsonResponse(['message' => 'Utilisateur invalide'], 401);
    }

    $jwt = $this->jwtManager->create($user);

    // Format minimaliste sans aucune redirection
    return new JsonResponse([
      'token' => $jwt,
      'user' => [
        'email' => $user->getUserIdentifier(),
        'roles' => $user->getRoles()
      ]
    ]);
  }
}
