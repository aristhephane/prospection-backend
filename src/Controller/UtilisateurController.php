<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/utilisateurs')]
class UtilisateurController extends AbstractController
{
    /**
     * Affiche la liste complète des utilisateurs.
     * Seul un administrateur ou le service informatique peut accéder à cette page.
     */
    #[Route('/', name: 'utilisateur_index')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        // Vérifie que l'utilisateur connecté possède le rôle ADMIN.
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupère tous les utilisateurs de la base de données.
        $utilisateurs = $utilisateurRepository->findAll();

        // Rendu du template Twig 'utilisateur/index.html.twig' avec la liste des utilisateurs.
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    /**
     * Permet à l'utilisateur connecté de modifier son profil.
     * Les champs modifiables sont : nom, email, mot de passe.
     */
    #[Route('/profil', name: 'utilisateur_profil')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur actuellement connecté.
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        // Crée le formulaire d'édition du profil à partir du type UtilisateurType.
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        // Si le formulaire est soumis et validé, on enregistre les modifications.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Enregistre les modifications en base de données.
            $this->addFlash('success', 'Votre profil a été mis à jour.');
            return $this->redirectToRoute('utilisateur_profil');
        }

        // Rendu du template avec le formulaire.
        return $this->render('utilisateur/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet aux administrateurs d'ajouter un nouvel utilisateur.
     * Les comptes sont créés par un administrateur.
     */
    #[Route('/ajouter', name: 'utilisateur_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Restriction d'accès : seuls les administrateurs peuvent ajouter des utilisateurs.
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Crée une nouvelle instance de Utilisateur.
        $utilisateur = new Utilisateur();

        // Crée le formulaire d'ajout d'utilisateur.
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vous pouvez ici intégrer la logique de hachage du mot de passe avec UserPasswordHasherInterface.
            $entityManager->persist($utilisateur);
            $entityManager->flush();
            $this->addFlash('success', 'Nouvel utilisateur créé.');
            return $this->redirectToRoute('utilisateur_index');
        }
        return $this->render('utilisateur/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche les détails d'un utilisateur (accessible aux administrateurs).
     */
    #[Route('/{id}', name: 'utilisateur_show')]
    public function show(Utilisateur $utilisateur): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Permet aux administrateurs de modifier un utilisateur.
     */
    #[Route('/{id}/modifier', name: 'utilisateur_edit')]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde les modifications.
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('utilisateur_index');
        }
        return $this->render('utilisateur/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet aux administrateurs de supprimer définitivement un utilisateur.
     */
    #[Route('/{id}/supprimer', name: 'utilisateur_delete')]
    public function delete(Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entityManager->remove($utilisateur);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('utilisateur_index');
    }

    /**
     * Permet aux administrateurs de désactiver temporairement un utilisateur.
     * Pour cela, nous utilisons un champ "actif" (booléen) dans l'entité Utilisateur.
     */
    #[Route('/{id}/désactiver', name: 'utilisateur_desactiver')]
    public function desactiver(Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // Met le champ "actif" à false pour désactiver l'utilisateur.
        $utilisateur->setActif(false);
        $entityManager->flush();
        $this->addFlash('success', "L'utilisateur a été désactivé.");
        return $this->redirectToRoute('utilisateur_index');
    }
}
