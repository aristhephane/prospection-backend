<?php
// src/Controller/HistoriqueModificationController.php

namespace App\Controller;

use App\Repository\HistoriqueModificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/historique')]
class HistoriqueModificationController extends AbstractController
{
    /**
     * Affiche la liste globale de l'historique des modifications.
     */
    #[Route('/', name: 'historique_index', methods: ['GET'])]
    public function index(HistoriqueModificationRepository $historiqueRepo): Response
    {
        try {
            $modifications = $historiqueRepo->findBy([], ['dateModification' => 'DESC']);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement de l’historique.');
            $modifications = [];
        }
        return $this->render('historique/index.html.twig', [
            'modifications' => $modifications,
        ]);
    }

     /**
     * Affiche le détail d'une modification spécifique.
     */
    #[Route('/{id}', name: 'historique_show', methods: ['GET'])]
    public function show(\App\Entity\HistoriqueModification $historique): Response
    {
        return $this->render('historique/show.html.twig', [
            'historique' => $historique,
        ]);
}
}
