<?php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use App\Repository\FicheEntrepriseRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/recherche')]
#[IsGranted('ROLE_USER')] // Ajoute une vérification globale pour toutes les méthodes du contrôleur
class SearchController extends AbstractController
{
    /**
     * Recherche avancée pour les entreprises et fiches entreprises.
     */
    #[Route('/', name: 'search_index', methods: ['GET'])]
    public function index(
        Request $request,
        EntrepriseRepository $entrepriseRepo,
        FicheEntrepriseRepository $ficheRepo,
        PaginatorInterface $paginator
    ): Response {
        $criteria = [
            'nom' => $request->query->get('name', ''),
            'secteurActivite' => $request->query->get('sector', ''),
            'tailleEntreprise' => $request->query->get('size', ''),
            'dateCreation' => $request->query->get('date', ''),
            'commentaires' => $request->query->get('comment', ''),
        ];

        try {
            // Recherche des entreprises
            $queryEntreprises = $entrepriseRepo->searchEntreprises($criteria);

            // Recherche des fiches entreprises
            $queryFiches = $ficheRepo->searchFiches($criteria);

            // Pagination pour éviter un affichage trop long
            $paginationEntreprises = $paginator->paginate(
                $queryEntreprises,
                $request->query->getInt('page_entreprises', 1),
                20
            );

            $paginationFiches = $paginator->paginate(
                $queryFiches,
                $request->query->getInt('page_fiches', 1),
                20
            );

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la recherche.');
            $paginationEntreprises = [];
            $paginationFiches = [];
        }

        return $this->json([
            'success' => true,
            'results' => [
                'paginationEntreprises' => $paginationEntreprises ?? [],
                'paginationFiches' => $paginationFiches ?? [],
                'filters' => $criteria,
            ]
        ]);
    }
}