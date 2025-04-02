<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:test-auth',
    description: 'Test the authentication system'
)]
class TestAuthCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Test d\'authentification');

        // Test de récupération de l'utilisateur
        $io->section('Récupération de l\'utilisateur');
        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $user = $utilisateurRepository->findOneBy(['email' => 'admin@upjv.fr']);

        if (!$user) {
            $io->error('Utilisateur "admin@upjv.fr" non trouvé !');
            return Command::FAILURE;
        }

        $io->success('Utilisateur trouvé : ' . $user->getEmail());
        $io->writeln('Nom complet : ' . $user->getPrenom() . ' ' . $user->getNom());
        $io->writeln('Rôles : ' . implode(', ', $user->getRoles()));
        $io->writeln('Type d\'interface : ' . $user->getTypeInterface());
        $io->writeln('Hash du mot de passe : ' . $user->getPassword());

        // Test de vérification du mot de passe
        $io->section('Vérification du mot de passe');

        $password = 'Admin@2025';
        $isValid = $this->passwordHasher->isPasswordValid($user, $password);

        if ($isValid) {
            $io->success('Le mot de passe est valide.');
        } else {
            $io->error('Le mot de passe est invalide !');
            
            // Afficher plus de détails
            $io->writeln('Format du hash stocké : ' . $user->getPassword());
            
            // Générer un nouveau hash pour comparaison
            $newHash = $this->passwordHasher->hashPassword($user, $password);
            $io->writeln('Nouveau hash généré : ' . $newHash);
            
            // Créer un utilisateur test
            $io->section('Test avec un nouvel utilisateur');
            $testUser = new Utilisateur();
            $testUser->setEmail('test@example.com');
            $testUser->setNom('Test');
            $testUser->setPrenom('User');
            $testUser->setDateCreation(new \DateTime());
            $testUser->setActif(true);
            
            $testHash = $this->passwordHasher->hashPassword($testUser, $password);
            $testUser->setPassword($testHash);
            
            $io->writeln('Hash test : ' . $testHash);
            $io->writeln('Vérification : ' . ($this->passwordHasher->isPasswordValid($testUser, $password) ? 'Valide' : 'Invalide'));
        }

        // Test des clés JWT
        $io->section('Vérification de la configuration JWT');
        
        $privateKeyPath = $this->getApplication()->getKernel()->getProjectDir() . '/config/jwt/private.pem';
        $publicKeyPath = $this->getApplication()->getKernel()->getProjectDir() . '/config/jwt/public.pem';
        
        $privateKeyExists = file_exists($privateKeyPath);
        $publicKeyExists = file_exists($publicKeyPath);
        
        if (!$privateKeyExists || !$publicKeyExists) {
            $io->error('Les clés JWT n\'existent pas !');
            return Command::FAILURE;
        }
        
        $io->success('Les clés JWT existent');
        $io->writeln('Clé privée : ' . $privateKeyPath . ' (' . substr(sprintf('%o', fileperms($privateKeyPath)), -4) . ')');
        $io->writeln('Clé publique : ' . $publicKeyPath . ' (' . substr(sprintf('%o', fileperms($publicKeyPath)), -4) . ')');

        // Résumé
        $io->section('Résumé des tests');
        $io->writeln('- Récupération de l\'utilisateur : ' . ($user ? '✅' : '❌'));
        $io->writeln('- Vérification du mot de passe : ' . ($isValid ? '✅' : '❌'));
        $io->writeln('- Configuration JWT : ' . (($privateKeyExists && $publicKeyExists) ? '✅' : '❌'));

        if ($isValid) {
            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }
    }
}
