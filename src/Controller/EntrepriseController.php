<?php
// src/Controller/EntrepriseController.php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Form\EntrepriseType;
use App\Repository\EntrepriseRepository;
use App\Service\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entreprises')]
class EntrepriseController extends AbstractController
{
     /**
     * Affiche la liste des entreprises.
     */
    #[Route('/', name: 'entreprise_index', methods: ['GET'])]
    public function index(EntrepriseRepository $entrepriseRepository): Response
    {
        try {
            $entreprises = $entrepriseRepository->findAll();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement des entreprises.');
            $entreprises = [];
        }
        
        return $this->render('entreprise/index.html.twig', [
            'entreprises' => $entreprises,
        ]);
    }

     /**
     * Affiche le formulaire pour ajouter une nouvelle entreprise.
     */
    #[Route('/ajouter', name: 'entreprise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour une nouvelle entreprise, on définit par défaut qu'elle n'est pas archivée.
            $entreprise->setArchive(false);
            try {
                $entityManager->persist($entreprise);
                $entityManager->flush();
                $this->addFlash('success', 'Entreprise créée avec succès.');
                return $this->redirectToRoute('entreprise_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de l’entreprise.');
            }
        }

        return $this->render('entreprise/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche le détail d'une entreprise.
     */
    #[Route('/{id}', name: 'entreprise_show', methods: ['GET'])]
    public function show(Entreprise $entreprise): Response
    {
        return $this->render('entreprise/show.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }

    /**
     * Affiche le formulaire pour modifier une entreprise.
     */
    #[Route('/{id}/modifier', name: 'entreprise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Entreprise modifiée avec succès.');
                return $this->redirectToRoute('entreprise_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification de l’entreprise.');
            }
        }

        return $this->render('entreprise/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Supprime définitivement une entreprise.
     * Cette action est réservée aux administrateurs.
     */
    #[Route('/{id}/supprimer', name: 'entreprise_delete', methods: ['POST'])]
    public function delete(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        // Vérification de l'accès : cette action est réservée aux administrateurs
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete'.$entreprise->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($entreprise);
                $entityManager->flush();
                $this->addFlash('success', 'Entreprise supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression de l’entreprise.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('entreprise_index');
    }

    /**
     * Archive une entreprise pour la masquer sans la supprimer définitivement.
     */
    #[Route('/{id}/archiver', name: 'entreprise_archive')]
    public function archive(Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        // Met à jour le champ "archive" pour masquer l'entreprise.
        $entreprise->setArchive(true);
        $entityManager->flush();
        $this->addFlash('success', 'Entreprise archivée.');
        return $this->redirectToRoute('entreprise_index');
    }

    /**
     * Exporte la liste des entreprises en PDF ou Excel.
     * Cette action est réservée aux administrateurs.
     */
    #[Route('/export', name: 'entreprise_export', methods: ['GET'])]
    public function export(EntrepriseRepository $entrepriseRepository, ExportService $exportService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $entreprises = $entrepriseRepository->findAll();
            // Choix du format peut être passé en paramètre (ici, on utilise 'excel' par défaut)
            return $exportService->generateExport($entreprises, 'excel');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l’export des entreprises.');
            return $this->redirectToRoute('entreprise_index');
        }
    }
}