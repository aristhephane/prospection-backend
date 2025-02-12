<?php

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
    #[Route('/', name: 'historique_index')]
    public function index(HistoriqueModificationRepository $historiqueRepo): Response
    {
        $historiques = $historiqueRepo->findAll();
        return $this->render('historique/index.html.twig', [
            'historiques' => $historiques,
        ]);
    }

    /**
     * Affiche le détail d'une modification spécifique.
     */
    #[Route('/{id}', name: 'historique_show')]
    public function show(\App\Entity\HistoriqueModification $historique): Response
    {
        return $this->render('historique/show.html.twig', [
            'historique' => $historique,
        ]);
    }
}
