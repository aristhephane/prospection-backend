<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResetPasswordService
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private TokenGeneratorInterface $tokenGenerator;
    private UserPasswordHasherInterface $passwordHasher;
    private UrlGeneratorInterface $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
        UserPasswordHasherInterface $passwordHasher,
        UrlGeneratorInterface $router
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordHasher = $passwordHasher;
        $this->router = $router;
    }

    /**
     * Génère un token sécurisé et envoie un e-mail avec un lien de réinitialisation.
     */
    public function sendResetEmail(Utilisateur $user): void
    {
        $token = $this->tokenGenerator->generateToken();
        $user->setResetToken($token);
        $user->setTokenExpiration(new \DateTime('+1 hour'));

        $this->entityManager->flush();

        $resetUrl = $this->router->generate('reset_password_confirm', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('no-reply@exemple.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html("<p>Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :</p><a href='{$resetUrl}'>Réinitialiser mon mot de passe</a>");

        $this->mailer->send($email);
    }

    /**
     * Valide un token et met à jour le mot de passe.
     */
    public function processReset(Utilisateur $user, string $newPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setResetToken(null);
        $user->setTokenExpiration(null);

        $this->entityManager->flush();
    }
}
