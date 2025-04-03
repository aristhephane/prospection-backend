<?php
// Script pour purger et recréer des utilisateurs dans Symfony

require dirname(__FILE__).'/vendor/autoload.php';

use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Entity\Permission;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

// Chargement des variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__FILE__).'/.env');

// Initialisation du kernel Symfony
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// Récupération des services
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine.orm.entity_manager');
$passwordHasher = $container->get('security.user_password_hasher');

echo "Début du processus de purge et recréation des utilisateurs...\n";

try {
    // 1. Supprimer les associations utilisateur-rôle (table user_role)
    $connection = $entityManager->getConnection();
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
    
    // Suppression des données des tables liées
    try {
        $connection->executeStatement('TRUNCATE TABLE user_role');
        echo "Table user_role vidée.\n";
    } catch (\Exception $e) {
        echo "Erreur lors de la suppression des relations utilisateur-rôle: " . $e->getMessage() . "\n";
    }
    
    // 2. Supprimer tous les utilisateurs
    try {
        // Désactiver les utilisateurs plutôt que les supprimer pour éviter les problèmes de clés étrangères
        $utilisateurRepository = $entityManager->getRepository(Utilisateur::class);
        $utilisateurs = $utilisateurRepository->findAll();
        
        foreach ($utilisateurs as $utilisateur) {
            $entityManager->remove($utilisateur);
        }
        $entityManager->flush();
        echo count($utilisateurs) . " utilisateurs supprimés.\n";
    } catch (\Exception $e) {
        echo "Erreur lors de la suppression des utilisateurs: " . $e->getMessage() . "\n";
    }
    
    // 3. Supprimer les rôles existants
    try {
        $roleRepository = $entityManager->getRepository(Role::class);
        $roles = $roleRepository->findAll();
        
        foreach ($roles as $role) {
            $entityManager->remove($role);
        }
        $entityManager->flush();
        echo count($roles) . " rôles supprimés.\n";
    } catch (\Exception $e) {
        echo "Erreur lors de la suppression des rôles: " . $e->getMessage() . "\n";
    }
    
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    
    // 4. Création des permissions de base
    $permissionLecture = new Permission();
    $permissionLecture->setNomPermission('Lecture');
    $permissionLecture->setDescription('Permission de lecture des données');
    $entityManager->persist($permissionLecture);
    
    $permissionEcriture = new Permission();
    $permissionEcriture->setNomPermission('Écriture');
    $permissionEcriture->setDescription('Permission de modification des données');
    $entityManager->persist($permissionEcriture);
    
    $permissionAdmin = new Permission();
    $permissionAdmin->setNomPermission('Administration');
    $permissionAdmin->setDescription('Permission d\'administration du système');
    $entityManager->persist($permissionAdmin);
    
    $entityManager->flush();
    echo "Permissions de base créées.\n";
    
    // 5. Création des rôles selon la structure Entity Role
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
    $entityManager->persist($adminRole);
    
    $prospectionRole = new Role();
    $prospectionRole->setNom(Role::ROLE_PROSPECTION);
    $prospectionRole->setDescription('Service de prospection');
    $prospectionRole->setModificationDonnees(true);
    $prospectionRole->setAccesRapports(true);
    $prospectionRole->setTypeAccesFiches('Lecture/Écriture');
    $prospectionRole->addPermission($permissionLecture);
    $prospectionRole->addPermission($permissionEcriture);
    $entityManager->persist($prospectionRole);
    
    $enseignantRole = new Role();
    $enseignantRole->setNom(Role::ROLE_ENSEIGNANT);
    $enseignantRole->setDescription('Enseignants/Formateurs');
    $enseignantRole->setAccesRapports(true);
    $enseignantRole->setTypeAccesFiches('Lecture');
    $enseignantRole->addPermission($permissionLecture);
    $entityManager->persist($enseignantRole);
    
    // On flush pour que les rôles soient persistés et aient des IDs
    $entityManager->flush();
    echo "Rôles créés avec succès.\n";
    
    // 6. Création des utilisateurs
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
        try {
            $user = new Utilisateur();
            $user->setEmail($userData['email']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setDateCreation(new \DateTime());
            $user->setActif(true);
            $user->setTypeInterface($userData['type']);
            
            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            
            // Attribution des rôles
            foreach ($userData['roles'] as $role) {
                $user->addRole($role);
            }
            
            $entityManager->persist($user);
            
            $createdUsers[] = [
                'email' => $userData['email'],
                'password' => $userData['password'],
                'roles' => array_map(function($role) { return $role->getNom(); }, $userData['roles']),
                'status' => 'created'
            ];
        } catch (\Exception $e) {
            echo "Erreur lors de la création de l'utilisateur {$userData['email']}: " . $e->getMessage() . "\n";
        }
    }
    
    $entityManager->flush();
    
    // Affichage des utilisateurs créés
    echo "\n=== Utilisateurs créés ===\n\n";
    foreach ($createdUsers as $user) {
        echo "Email: " . $user['email'] . "\n";
        echo "Mot de passe: " . $user['password'] . "\n";
        echo "Rôles: " . implode(', ', $user['roles']) . "\n";
        echo "Statut: " . $user['status'] . "\n\n";
    }
} catch (\Exception $e) {
    echo "Erreur générale: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} finally {
    // Toujours réactiver les vérifications de clés étrangères
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
}

echo "Script terminé.\n"; 