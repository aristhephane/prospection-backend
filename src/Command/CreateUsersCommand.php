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
  name: 'app:create-users',
  description: 'Creates admin and regular users for testing'
)]
class CreateUsersCommand extends Command
{
  public function __construct(
    private EntityManagerInterface $entityManager,
    private UserPasswordHasherInterface $passwordHasher
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    // Create roles if they don't exist
    $roleAdmin = $this->entityManager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_ADMIN']);
    if (!$roleAdmin) {
      $roleAdmin = new Role();
      $roleAdmin->setNom('ROLE_ADMIN');
      $roleAdmin->setDescription('Administrateur avec tous les droits');
      $this->entityManager->persist($roleAdmin);
      $io->success('Role ROLE_ADMIN created');
    }

    $roleUser = $this->entityManager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_USER']);
    if (!$roleUser) {
      $roleUser = new Role();
      $roleUser->setNom('ROLE_USER');
      $roleUser->setDescription('Utilisateur standard');
      $this->entityManager->persist($roleUser);
      $io->success('Role ROLE_USER created');
    }

    // Assurez-vous de sauvegarder les rôles avant de les utiliser
    $this->entityManager->flush();

    // Create admin user
    $admin = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => 'admin@example.com']);
    if (!$admin) {
      $admin = new Utilisateur();
      $admin->setNom('Admin');
      $admin->setPrenom('Super');
      $admin->setEmail('admin@example.com');

      $plainPassword = 'Admin123!';
      $hashedPassword = $this->passwordHasher->hashPassword($admin, $plainPassword);
      $admin->setPassword($hashedPassword);

      $admin->setActif(true);
      $admin->addRole($roleAdmin);

      $this->entityManager->persist($admin);
      $io->success('Admin user created: admin@example.com / Admin123!');

      // Test de vérification du mot de passe
      if ($this->passwordHasher->isPasswordValid($admin, $plainPassword)) {
        $io->success('Password verification successful for admin user!');
      } else {
        $io->error('Password verification failed for admin user!');
      }
    } else {
      $io->note('Admin user already exists');

      // Vérifiez si le mot de passe est correct pour l'utilisateur existant
      if ($this->passwordHasher->isPasswordValid($admin, 'Admin123!')) {
        $io->success('Password verification successful for existing admin user!');
      } else {
        $io->error('Password verification failed for existing admin user!');

        // Mise à jour du mot de passe si nécessaire
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'Admin123!');
        $admin->setPassword($hashedPassword);
        $this->entityManager->persist($admin);
        $io->success('Admin password updated');
      }
    }

    // Create regular user
    $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => 'user@example.com']);
    if (!$user) {
      $user = new Utilisateur();
      $user->setNom('User');
      $user->setPrenom('Regular');
      $user->setEmail('user@example.com');

      $plainPassword = 'User123!';
      $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
      $user->setPassword($hashedPassword);

      $user->setActif(true);
      $user->addRole($roleUser);

      $this->entityManager->persist($user);
      $io->success('Regular user created: user@example.com / User123!');

      // Test de vérification du mot de passe
      if ($this->passwordHasher->isPasswordValid($user, $plainPassword)) {
        $io->success('Password verification successful for regular user!');
      } else {
        $io->error('Password verification failed for regular user!');
      }
    } else {
      $io->note('Regular user already exists');

      // Vérifiez si le mot de passe est correct pour l'utilisateur existant
      if ($this->passwordHasher->isPasswordValid($user, 'User123!')) {
        $io->success('Password verification successful for existing regular user!');
      } else {
        $io->error('Password verification failed for existing regular user!');

        // Mise à jour du mot de passe si nécessaire
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'User123!');
        $user->setPassword($hashedPassword);
        $this->entityManager->persist($user);
        $io->success('Regular user password updated');
      }
    }

    $this->entityManager->flush();

    $io->success('Users created successfully. You can now log in with these credentials.');

    return Command::SUCCESS;
  }
}
