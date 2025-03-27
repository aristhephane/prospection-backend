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
  description: 'Test authentication for created users'
)]
class TestAuthCommand extends Command
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

    // Test admin user
    $admin = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => 'admin@example.com']);

    if (!$admin) {
      $io->error('Admin user not found! Run app:create-users first.');
      return Command::FAILURE;
    }

    $io->section('User Information');
    $io->table(
      ['Email', 'Roles', 'Password Valid'],
      [
        [
          $admin->getEmail(),
          implode(', ', $admin->getRoles()),
          $this->passwordHasher->isPasswordValid($admin, 'Admin123!') ? 'Yes' : 'No'
        ]
      ]
    );

    $io->section('Authentication Test Commands');

    $io->writeln('CURL command for testing:');
    $io->writeln('```');
    $io->writeln('curl -X POST http://localhost/api/login \\');
    $io->writeln('-H "Content-Type: application/json" \\');
    $io->writeln('-d \'{"email": "admin@example.com", "password": "Admin123!"}\'');
    $io->writeln('```');

    $io->writeln('For the test endpoint:');
    $io->writeln('```');
    $io->writeln('curl -X POST http://localhost/api/auth-test \\');
    $io->writeln('-H "Content-Type: application/json" \\');
    $io->writeln('-d \'{"test": "data"}\'');
    $io->writeln('```');

    return Command::SUCCESS;
  }
}
