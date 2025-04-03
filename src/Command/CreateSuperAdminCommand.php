<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-superadmin',
    description: 'Create a super admin user with a simple password'
)]
class CreateSuperAdminCommand extends Command
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
        $io->title('Création d\'un super administrateur');

        try {
            // Vérifier si l'utilisateur existe déjà
            $userRepo = $this->entityManager->getRepository(Utilisateur::class);
            $user = $userRepo->findOneBy(['email' => 'superadmin@upjv.fr']);

            if ($user) {
                $io->note('L\'utilisateur superadmin@upjv.fr existe déjà');
                $io->note('Mise à jour du mot de passe...');
            } else {
                $io->note('Création de l\'utilisateur superadmin@upjv.fr...');

                $user = new Utilisateur();
                $user->setEmail('superadmin@upjv.fr');
                $user->setNom('SUPER');
                $user->setPrenom('Admin');
                $user->setDateCreation(new \DateTime());
                $user->setActif(true);
                $user->setTypeInterface('administrateur');

                // Ajouter le rôle administrateur
                $roleRepo = $this->entityManager->getRepository(Role::class);
                $adminRole = $roleRepo->findOneBy(['nom' => 'administrateur']);

                if (!$adminRole) {
                    $io->error('Rôle administrateur non trouvé');
                    return Command::FAILURE;
                }

                $user->addRole($adminRole);
            }

            // Définir un mot de passe très simple à taper
            $password = 'admin123';
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('Utilisateur superadmin@upjv.fr créé/mis à jour avec succès');
            $io->table(
                ['Email', 'Mot de passe', 'Hash'],
                [
                    ['superadmin@upjv.fr', $password, $hashedPassword]
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Une erreur est survenue: ' . $e->getMessage());
            $io->writeln($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
} 