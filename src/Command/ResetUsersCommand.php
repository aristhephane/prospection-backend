<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Entity\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:reset-users',
    description: 'Reset all users and recreate fresh admin and regular users'
)]
class ResetUsersCommand extends Command
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
        $connection = $this->entityManager->getConnection();

        try {
            // Désactiver temporairement les vérifications de clés étrangères
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
            
            // 1. Supprimer les associations utilisateur-rôle
            $connection->executeStatement('DELETE FROM user_role');
            $io->success('Associations utilisateur-rôle supprimées');

            // 2. Supprimer tous les utilisateurs
            $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
            $utilisateurs = $utilisateurRepository->findAll();
            
            foreach ($utilisateurs as $utilisateur) {
                $this->entityManager->remove($utilisateur);
            }
            $this->entityManager->flush();
            $io->success(count($utilisateurs) . ' utilisateurs supprimés');
            
            // 3. Supprimer tous les rôles existants
            $roleRepository = $this->entityManager->getRepository(Role::class);
            $roles = $roleRepository->findAll();
            
            foreach ($roles as $role) {
                $this->entityManager->remove($role);
            }
            $this->entityManager->flush();
            $io->success(count($roles) . ' rôles supprimés');
            
            // Réactiver les vérifications de clés étrangères
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            
            // 4. Créer les permissions de base
            $permissionLecture = new Permission();
            $permissionLecture->setNomPermission('Lecture');
            $permissionLecture->setDescription('Permission de lecture des données');
            $this->entityManager->persist($permissionLecture);
            
            $permissionEcriture = new Permission();
            $permissionEcriture->setNomPermission('Écriture');
            $permissionEcriture->setDescription('Permission de modification des données');
            $this->entityManager->persist($permissionEcriture);
            
            $permissionAdmin = new Permission();
            $permissionAdmin->setNomPermission('Administration');
            $permissionAdmin->setDescription('Permission d\'administration du système');
            $this->entityManager->persist($permissionAdmin);
            
            $this->entityManager->flush();
            $io->success('Permissions de base créées');
            
            // 5. Créer les rôles standards conformément à la classe Role
            $adminRole = new Role();
            $adminRole->setNom(Role::ROLE_ADMIN);
            $adminRole->setDescription('Administrateur du système');
            $adminRole->setAdministrationSysteme(true);
            $adminRole->setModificationDonnees(true);
            $adminRole->setAccesRapports(true);
            $adminRole->setTypeAccesFiches('Lecture/Écriture');
            $adminRole->addPermission($permissionLecture);
            $adminRole->addPermission($permissionEcriture);
            $adminRole->addPermission($permissionAdmin);
            $this->entityManager->persist($adminRole);
            
            $prospectionRole = new Role();
            $prospectionRole->setNom(Role::ROLE_PROSPECTION);
            $prospectionRole->setDescription('Service de prospection');
            $prospectionRole->setModificationDonnees(true);
            $prospectionRole->setAccesRapports(true);
            $prospectionRole->setTypeAccesFiches('Lecture/Écriture');
            $prospectionRole->addPermission($permissionLecture);
            $prospectionRole->addPermission($permissionEcriture);
            $this->entityManager->persist($prospectionRole);
            
            $enseignantRole = new Role();
            $enseignantRole->setNom(Role::ROLE_ENSEIGNANT);
            $enseignantRole->setDescription('Enseignants/Formateurs');
            $enseignantRole->setAccesRapports(true);
            $enseignantRole->setTypeAccesFiches('Lecture');
            $enseignantRole->addPermission($permissionLecture);
            $this->entityManager->persist($enseignantRole);
            
            $this->entityManager->flush();
            $io->success('Rôles créés avec succès');
            
            // 6. Créer les utilisateurs standards
            $users = [
                // Administrateurs
                [
                    'email' => 'admin@upjv.fr',
                    'password' => 'Admin@2025',
                    'nom' => 'ADMIN',
                    'prenom' => 'Système',
                    'roles' => [$adminRole],
                    'type' => 'administrateur'
                ],
                [
                    'email' => 'directeur@upjv.fr',
                    'password' => 'Director@2025',
                    'nom' => 'MARTIN',
                    'prenom' => 'Jean',
                    'roles' => [$adminRole],
                    'type' => 'administrateur'
                ],
                // Utilisateurs standard
                [
                    'email' => 'prospecteur@upjv.fr',
                    'password' => 'Prospect@2025',
                    'nom' => 'DURAND',
                    'prenom' => 'Marie',
                    'roles' => [$prospectionRole],
                    'type' => 'utilisateur'
                ],
                [
                    'email' => 'enseignant@upjv.fr',
                    'password' => 'Teach@2025',
                    'nom' => 'DUPONT',
                    'prenom' => 'Pierre',
                    'roles' => [$enseignantRole],
                    'type' => 'utilisateur'
                ]
            ];
            
            $createdUsers = [];
            foreach ($users as $userData) {
                $user = new Utilisateur();
                $user->setEmail($userData['email']);
                $user->setNom($userData['nom']);
                $user->setPrenom($userData['prenom']);
                $user->setDateCreation(new \DateTime());
                $user->setActif(true);
                $user->setTypeInterface($userData['type']);
                
                // Hashage du mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
                $user->setPassword($hashedPassword);
                
                // Attribution des rôles
                foreach ($userData['roles'] as $role) {
                    $user->addRole($role);
                }
                
                $this->entityManager->persist($user);
                
                $createdUsers[] = [
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'roles' => array_map(function($role) { return $role->getNom(); }, $userData['roles'])
                ];
            }
            
            $this->entityManager->flush();
            
            // Affichage des utilisateurs créés
            $io->section('Utilisateurs créés');
            
            foreach ($createdUsers as $user) {
                $io->writeln(sprintf(
                    "<info>Email:</info> %s\n<info>Mot de passe:</info> %s\n<info>Rôles:</info> %s\n",
                    $user['email'],
                    $user['password'],
                    implode(', ', $user['roles'])
                ));
            }
            
            $io->success('Réinitialisation des utilisateurs terminée avec succès');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Réactiver les vérifications de clés étrangères en cas d'erreur
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            
            $io->error('Une erreur est survenue : ' . $e->getMessage());
            $io->error($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
} 