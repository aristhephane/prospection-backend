<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Affiche la page de connexion.
     */
    #[Route('/login', name: 'security_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_index'); // Redirection vers le tableau de bord
        }

        // Récupération des erreurs d'authentification et du dernier nom d'utilisateur
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * La déconnexion est gérée par Symfony.
     * Cette méthode ne sera jamais exécutée, elle est interceptée par le firewall.
     */
    #[Route('/logout', name: 'security_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Le contrôleur logout peut rester vide : la gestion se fait via la configuration du firewall.
        throw new \Exception('Cette méthode ne doit pas être appelée directement.');
    }
}
