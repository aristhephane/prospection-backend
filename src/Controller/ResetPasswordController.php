<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ResetPasswordType;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    private ResetPasswordService $resetPasswordService;

    public function __construct(ResetPasswordService $resetPasswordService)
    {
        $this->resetPasswordService = $resetPasswordService;
    }

    /**
     * Affiche le formulaire pour demander une réinitialisation de mot de passe.
     */
    #[Route('/', name: 'reset_password_request', methods: ['GET', 'POST'])]
    public function request(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResetPasswordType::class, null, ['reset_request' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            if ($user) {
                $this->resetPasswordService->sendResetEmail($user);
                $this->addFlash('success', 'Un e-mail de réinitialisation a été envoyé.');
            } else {
                $this->addFlash('error', 'Aucun compte associé à cet e-mail.');
            }

            return $this->redirectToRoute('security_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Confirme la réinitialisation avec un token sécurisé.
     */
    #[Route('/confirm/{token}', name: 'reset_password_confirm', methods: ['GET', 'POST'])]
    public function confirm(string $token, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getTokenExpiration() < new \DateTime()) {
            $this->addFlash('error', 'Le lien de réinitialisation est invalide ou expiré.');
            return $this->redirectToRoute('reset_password_request');
        }

        $form = $this->createForm(ResetPasswordType::class, null, ['reset_request' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->resetPasswordService->processReset($user, $form->get('plainPassword')->getData());
                $this->addFlash('success', 'Mot de passe réinitialisé avec succès.');
                return $this->redirectToRoute('security_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la réinitialisation du mot de passe.');
            }
        }

        return $this->render('reset_password/confirm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
