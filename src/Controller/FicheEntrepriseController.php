<?php

namespace App\Controller;

use App\Entity\FicheEntreprise;
use App\Entity\HistoriqueModification;
use App\Form\FicheEntrepriseType;
use App\Repository\FicheEntrepriseRepository;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fiches')]
class FicheEntrepriseController extends AbstractController
{
    /**
     * Affiche la liste de toutes les fiches entreprises.
     */
    #[Route('/', name: 'fiche_index')]
    public function index(FicheEntrepriseRepository $repository): Response
    {
        $fiches = $repository->findAll();
        return $this->render('fiche/index.html.twig', [
            'fiches' => $fiches,
        ]);
    }

    /**
     * Affiche les détails d'une fiche entreprise.
     */
    #[Route('/{id}', name: 'fiche_show')]
    public function show(FicheEntreprise $fiche): Response
    {
        return $this->render('fiche/show.html.twig', [
            'fiche' => $fiche,
        ]);
    }

    /**
     * Crée une nouvelle fiche entreprise.
     */
    #[Route('/nouvelle', name: 'fiche_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fiche = new FicheEntreprise();
        $form = $this->createForm(FicheEntrepriseType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définit la date de création et associe le créateur (utilisateur connecté)
            $fiche->setDateCreation(new \DateTime());
            $fiche->setCreePar($this->getUser());

            $entityManager->persist($fiche);
            $entityManager->flush();

            $this->addFlash('success', 'Fiche entreprise créée.');
            return $this->redirectToRoute('fiche_index');
        }
        return $this->render('fiche/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifie une fiche entreprise et enregistre l'historique de la modification.
     */
    #[Route('/{id}/modifier', name: 'fiche_edit')]
    public function edit(Request $request, FicheEntreprise $fiche, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FicheEntrepriseType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Met à jour la date de modification
            $fiche->setDateModification(new \DateTime());

            // Crée un enregistrement dans l'historique des modifications
            $historique = new HistoriqueModification();
            $historique->setDateModification(new \DateTime());
            $historique->setDetailsModification('Modification effectuée par ' . $this->getUser()->getUserIdentifier());
            $historique->setFicheEntreprise($fiche);
            $historique->setUtilisateur($this->getUser());
            $entityManager->persist($historique);

            $entityManager->flush();
            $this->addFlash('success', 'Fiche modifiée et historique enregistré.');
            return $this->redirectToRoute('fiche_index');
        }
        return $this->render('fiche/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Valide les modifications d'une fiche entreprise.
     * On suppose que l'entité FicheEntreprise possède un champ "valide" (booléen).
     */
    #[Route('/{id}/valider', name: 'fiche_valider')]
    public function valider(FicheEntreprise $fiche, EntityManagerInterface $entityManager): Response
    {
        // Marque la fiche comme validée
        $fiche->setValide(true);
        $entityManager->flush();
        $this->addFlash('success', 'La fiche a été validée.');
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
    public function historique(FicheEntreprise $fiche): Response
    {
        $historiques = $fiche->getHistoriqueModification();
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
            $fiche->setCommentaireInterne($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire ajouté.');
        }
        return $this->redirectToRoute('fiche_show', ['id' => $fiche->getId()]);
    }
}
