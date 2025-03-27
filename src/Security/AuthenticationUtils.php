<?php

namespace App\Security;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationUtils
{
  private $router;

  public function __construct(RouterInterface $router)
  {
    $this->router = $router;
  }

  /**
   * Génère une URL de redirection sécurisée après une connexion réussie
   * Vérifie si une route existe avant de tenter de générer son URL
   *
   * @param string $defaultRoute Route par défaut
   * @return string URL de redirection
   */
  public function getRedirectUrl(string $defaultRoute = 'dashboard_index'): string
  {
    try {
      // Essaie d'abord avec la route "dashboard"
      return $this->router->generate('dashboard');
    } catch (RouteNotFoundException $e) {
      try {
        // Si la route "dashboard" n'existe pas, utilise "dashboard_index"
        return $this->router->generate($defaultRoute);
      } catch (RouteNotFoundException $e) {
        // Si aucune de ces routes n'existe, retourne la racine
        return '/';
      }
    }
  }
}
