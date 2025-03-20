<?php
// src/Controller/FicheEntrepriseController.php

namespace App\Controller;

use App\Entity\FicheEntreprise;
use App\Entity\HistoriqueModification;
use App\Form\FicheEntrepriseType;
use App\Service\PdfGenerator; // Service pour générer des PDF
use App\Service\HistoriqueService;
use App\Repository\FicheEntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/fiches')]
#[IsGranted('ROLE_USER')]
class FicheEntrepriseController extends AbstractController
{
    /**
     * Affiche la liste de toutes les fiches entreprises.
     */
    #[Route('/', name: 'fiche_index', methods: ['GET'])]
    public function index(FicheEntrepriseRepository $repository): Response
    {
        try {
            $fiches = $repository->findAll();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement des fiches.');
            $fiches = [];
        }
        return $this->render('fiche/index.html.twig', [
            'fiches' => $fiches,
        ]);
    }

    /**
     * Affiche les détails d'une fiche entreprise.
     */
    #[Route('/{id}', name: 'fiche_show', methods: ['GET'])]
    public function show(FicheEntreprise $fiche): Response
    {
        return $this->render('fiche/show.html.twig', [
            'fiche' => $fiche,
        ]);
    }

    /**
     * Crée une nouvelle fiche entreprise.
     */
    #[Route('/nouvelle', name: 'fiche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fiche = new FicheEntreprise();
        $form = $this->createForm(FicheEntrepriseType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Définit la date de création et associe le créateur (utilisateur connecté)
                $fiche->setDateCreation(new \DateTime());
                $fiche->setCreePar($this->getUser());
                $entityManager->persist($fiche);
                $entityManager->flush();
                $this->addFlash('success', 'Fiche entreprise créée.');
                return $this->redirectToRoute('fiche_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de la fiche entreprise.');
            }
        }

        return $this->render('fiche/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifie une fiche entreprise et enregistre l'historique de la modification.
     */
    #[Route('/{id}/modifier', name: 'fiche_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        FicheEntreprise $fiche,
        EntityManagerInterface $entityManager,
        HistoriqueService $historiqueService
    ): Response {
        $form = $this->createForm(FicheEntrepriseType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Met à jour la date de modification
                $fiche->setDateModification(new \DateTime());

                // Utilisation du service pour enregistrer l'historique
                $historiqueService->enregistrerModification($fiche);

                $entityManager->flush();
                $this->addFlash('success', 'Fiche modifiée et historique enregistré.');
                return $this->redirectToRoute('fiche_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification de la fiche.');
            }
        }

        return $this->render('fiche/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Valide les modifications d'une fiche entreprise.
     * On suppose que l'entité FicheEntreprise possède un champ "valide" (booléen).
     */
    #[Route('/{id}/valider', name: 'fiche_valider', methods: ['POST'])]
    public function valider(Request $request, FicheEntreprise $fiche, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('valider' . $fiche->getId(), $request->request->get('_token'))) {
            try {
                $fiche->setValide(true);
                $entityManager->flush();
                $this->addFlash('success', 'La fiche a été validée.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la validation de la fiche.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        return $this->redirectToRoute('fiche_index');
    }

    /**
     * Génère un PDF prérempli pour une fiche entreprise.
     * Utilise le service PdfGenerator pour convertir un template Twig en PDF.
     */
    #[Route('/{id}/imprimer', name: 'fiche_imprimer')]
    public function imprimer(FicheEntreprise $fiche, PdfGenerator $pdfGenerator): Response
    {
        $data = [
            'fiche' => $fiche,
        ];
        $pdfContent = $pdfGenerator->generatePdf('fiche/pdf.html.twig', $data);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="fiche.pdf"',
        ]);
    }

    /**
     * Affiche l'historique des modifications d'une fiche entreprise.
     */
    #[Route('/{id}/historique', name: 'fiche_historique')]
    public function historique(FicheEntreprise $fiche, HistoriqueService $historiqueService): Response
    {
        $historiques = $historiqueService->getHistoriqueFiche($fiche);
        return $this->render('fiche/historique.html.twig', [
            'fiche' => $fiche,
            'historiques' => $historiques,
        ]);
    }

    /**
     * Permet d'ajouter un commentaire interne à une fiche entreprise.
     * Le commentaire est enregistré dans le champ "commentaireInterne" de l'entité FicheEntreprise.
     */
    #[Route('/{id}/commentaire', name: 'fiche_commentaire')]
    public function ajouterCommentaire(Request $request, FicheEntreprise $fiche, EntityManagerInterface $entityManager): Response
    {
        // Récupère le commentaire envoyé via POST (champ "commentaire").
        $commentaire = $request->request->get('commentaire');
        if ($commentaire) {
            $fiche->setCommentaires($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire ajouté.');
        }
        return $this->redirectToRoute('fiche_show', ['id' => $fiche->getId()]);
    }
}
