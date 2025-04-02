<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Permission;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des permissions
        $permissionLecture = new Permission();
        $permissionLecture->setNom('PERMISSION_LECTURE');
        $permissionLecture->setDescription('Permission de lecture des données');
        $manager->persist($permissionLecture);

        $permissionEcriture = new Permission();
        $permissionEcriture->setNom('PERMISSION_ECRITURE');
        $permissionEcriture->setDescription('Permission de modification des données');
        $manager->persist($permissionEcriture);

        $permissionRapports = new Permission();
        $permissionRapports->setNom('PERMISSION_RAPPORTS');
        $permissionRapports->setDescription('Permission d\'accès aux rapports');
        $manager->persist($permissionRapports);

        $permissionAdmin = new Permission();
        $permissionAdmin->setNom('PERMISSION_ADMIN');
        $permissionAdmin->setDescription('Permission d\'administration du système');
        $manager->persist($permissionAdmin);

        // Création des rôles selon la matrice
        $roleServiceProspection = new Role();
        $roleServiceProspection->setNom('ROLE_SERVICE_PROSPECTION');
        $roleServiceProspection->setDescription('Service de prospection');
        $roleServiceProspection->addPermission($permissionLecture);
        $roleServiceProspection->addPermission($permissionEcriture);
        $roleServiceProspection->addPermission($permissionRapports);
        $manager->persist($roleServiceProspection);

        $roleResponsableFormation = new Role();
        $roleResponsableFormation->setNom('ROLE_RESPONSABLE_FORMATION');
        $roleResponsableFormation->setDescription('Responsable de formation');
        $roleResponsableFormation->addPermission($permissionLecture);
        $roleResponsableFormation->addPermission($permissionRapports);
        $manager->persist($roleResponsableFormation);

        $roleDirection = new Role();
        $roleDirection->setNom('ROLE_DIRECTION');
        $roleDirection->setDescription('Direction');
        $roleDirection->addPermission($permissionLecture);
        $roleDirection->addPermission($permissionRapports);
        $manager->persist($roleDirection);

        $roleSecretariat = new Role();
        $roleSecretariat->setNom('ROLE_SECRETARIAT');
        $roleSecretariat->setDescription('Service secrétariat');
        $roleSecretariat->addPermission($permissionLecture);
        $roleSecretariat->addPermission($permissionEcriture);
        $manager->persist($roleSecretariat);

        $roleOrientation = new Role();
        $roleOrientation->setNom('ROLE_ORIENTATION');
        $roleOrientation->setDescription('Service d\'orientation');
        $roleOrientation->addPermission($permissionLecture);
        $roleOrientation->addPermission($permissionRapports);
        $manager->persist($roleOrientation);

        $roleInformatique = new Role();
        $roleInformatique->setNom('ROLE_INFORMATIQUE');
        $roleInformatique->setDescription('Service informatique');
        $roleInformatique->addPermission($permissionLecture);
        $roleInformatique->addPermission($permissionEcriture);
        $roleInformatique->addPermission($permissionRapports);
        $roleInformatique->addPermission($permissionAdmin);
        $manager->persist($roleInformatique);

        $roleEnseignant = new Role();
        $roleEnseignant->setNom('ROLE_ENSEIGNANT');
        $roleEnseignant->setDescription('Enseignants/Formateurs');
        $roleEnseignant->addPermission($permissionLecture);
        $roleEnseignant->addPermission($permissionRapports);
        $manager->persist($roleEnseignant);

        // Création de l'utilisateur administrateur
        $admin = new Utilisateur();
        $admin->setNom('Admin');
        $admin->setPrenom('Système');
        $admin->setEmail('admin@example.com');
        $admin->setActif(true);
        $admin->setDateCreation(new \DateTime());
        $admin->addRole($roleInformatique);

        // Hashage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'Admin123!'
        );
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        // Création de l'utilisateur standard
        $user = new Utilisateur();
        $user->setNom('Utilisateur');
        $user->setPrenom('Standard');
        $user->setEmail('user@example.com');
        $user->setActif(true);
        $user->setDateCreation(new \DateTime());
        $user->addRole($roleServiceProspection);

        // Hashage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'User123!'
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $manager->flush();
    }
}
