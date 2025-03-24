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
    $roleAdmin = $this->entityManager->getRepository(Role::class)->findOneBy(['nomRole' => 'ROLE_ADMIN']);
    if (!$roleAdmin) {
      $roleAdmin = new Role();
      $roleAdmin->setNomRole('ROLE_ADMIN');
      $roleAdmin->setDescription('Administrateur avec tous les droits');
      $this->entityManager->persist($roleAdmin);
      $io->success('Role ROLE_ADMIN created');
    }

    $roleUser = $this->entityManager->getRepository(Role::class)->findOneBy(['nomRole' => 'ROLE_USER']);
    if (!$roleUser) {
      $roleUser = new Role();
      $roleUser->setNomRole('ROLE_USER');
      $roleUser->setDescription('Utilisateur standard');
      $this->entityManager->persist($roleUser);
      $io->success('Role ROLE_USER created');
    }

    // Create admin user
    $admin = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => 'admin@example.com']);
    if (!$admin) {
      $admin = new Utilisateur();
      $admin->setNom('Admin');
      $admin->setPrenom('Super');
      $admin->setEmail('admin@example.com');

      // Using setPassword which will hash the password
      $hashedPassword = $this->passwordHasher->hashPassword($admin, 'Admin123!');
      $admin->setPassword($hashedPassword);

      $admin->setActif(true);
      $admin->addRole($roleAdmin);

      $this->entityManager->persist($admin);
      $io->success('Admin user created: admin@example.com / Admin123!');
    } else {
      $io->note('Admin user already exists');
    }

    // Create regular user
    $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => 'user@example.com']);
    if (!$user) {
      $user = new Utilisateur();
      $user->setNom('User');
      $user->setPrenom('Regular');
      $user->setEmail('user@example.com');

      // Using setPassword which will hash the password
      $hashedPassword = $this->passwordHasher->hashPassword($user, 'User123!');
      $user->setPassword($hashedPassword);

      $user->setActif(true);
      // No need to add ROLE_USER, as it's automatically assigned by the security system

      $this->entityManager->persist($user);
      $io->success('Regular user created: user@example.com / User123!');
    } else {
      $io->note('Regular user already exists');
    }

    $this->entityManager->flush();

    $io->success('Users created successfully. You can now log in with these credentials.');

    return Command::SUCCESS;
  }
}
