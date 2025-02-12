<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Form\EntrepriseType;
use App\Repository\EntrepriseRepository;
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
    #[Route('/', name: 'entreprise_index')]
    public function index(EntrepriseRepository $entrepriseRepository): Response
    {
        $entreprises = $entrepriseRepository->findAll();
        return $this->render('entreprise/index.html.twig', [
            'entreprises' => $entreprises,
        ]);
    }

    /**
     * Affiche le formulaire pour ajouter une nouvelle entreprise.
     */
    #[Route('/ajouter', name: 'entreprise_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour une nouvelle entreprise, on définit par défaut qu'elle n'est pas archivée.
            $entreprise->setArchive(false);
            $entityManager->persist($entreprise);
            $entityManager->flush();

            $this->addFlash('success', 'Entreprise créée avec succès.');
            return $this->redirectToRoute('entreprise_index');
        }
        return $this->render('entreprise/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche le détail d'une entreprise.
     */
    #[Route('/{id}', name: 'entreprise_show')]
    public function show(Entreprise $entreprise): Response
    {
        return $this->render('entreprise/show.html.twig', [
            'entreprise' => $entreprise,
        ]);
    }

    /**
     * Affiche le formulaire pour modifier une entreprise.
     */
    #[Route('/{id}/modifier', name: 'entreprise_edit')]
    public function edit(Request $request, Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Entreprise modifiée avec succès.');
            return $this->redirectToRoute('entreprise_index');
        }
        return $this->render('entreprise/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime définitivement une entreprise.
     * Cette action est réservée aux administrateurs.
     */
    #[Route('/{id}/supprimer', name: 'entreprise_delete')]
    public function delete(Entreprise $entreprise, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entityManager->remove($entreprise);
        $entityManager->flush();
        $this->addFlash('success', 'Entreprise supprimée avec succès.');
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
    #[Route('/export', name: 'entreprise_export')]
    public function export(EntrepriseRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // Ici, vous implémenterez la logique d'exportation.
        // Exemple : appel à un service d'export pour générer un fichier PDF ou Excel.
        return new Response('Export des entreprises en PDF/Excel');
    }
}
