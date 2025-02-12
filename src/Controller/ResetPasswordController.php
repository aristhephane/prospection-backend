<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use Symfony\Component\HttpFoundation\Request;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    /**
     * Affiche et traite le formulaire de demande de réinitialisation du mot de passe.
     */
    #[Route('/mot-de-passe/oubli', name: 'app_forgot_password')]
    public function request(Request $request): Response
    {
        // La méthode request() est gérée par ResetPasswordControllerTrait.
        // Le bundle fournit déjà un formulaire et une logique de traitement.
        return $this->render('security/forgot_password.html.twig');
    }

    /**
     * Affiche et traite le formulaire de réinitialisation du mot de passe.
     */
    #[Route('/mot-de-passe/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token = null): Response
    {
        // La méthode reset() est également gérée par ResetPasswordControllerTrait.
        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}
