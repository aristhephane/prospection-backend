<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-users',
    description: 'Liste tous les utilisateurs de l\'application',
)]
class ListUsersCommand extends Command
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Liste des utilisateurs');

        $users = $this->userRepository->findAll();

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user->getId(),
                $user->getEmail(),
                $user->getNom() . ' ' . $user->getPrenom(),
                implode(', ', $user->getRoles()),
            ];
        }

        $io->table(['ID', 'Email', 'Nom', 'Rôles'], $rows);

        return Command::SUCCESS;
    }
} 