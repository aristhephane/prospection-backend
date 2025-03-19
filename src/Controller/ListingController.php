<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\EntrepriseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ExportService;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/listing')]
class ListingController extends AbstractController
{
    /**
     * Affiche un listing paginé avec filtres appliqués.
     */
    #[Route('/', name: 'listing_index', methods: ['GET'])]
    public function index(
        Request $request,
        EntrepriseRepository $entrepriseRepo,
        PaginatorInterface $paginator
    ): Response {
        $filters = [
            'nom' => $request->query->get('name'),
            'secteurActivite' => $request->query->get('sector'),
            'tailleEntreprise' => $request->query->get('size'),
            'dateCreation' => $request->query->get('date'),
        ];

        try {
            // Recherche des entreprises filtrées
            $query = $entrepriseRepo->searchEntreprises($filters);
            
            // Pagination des résultats
            $pagination = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                20 // Nombre d'éléments par page
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement de la liste.');
            $pagination = [];
        }

        return $this->render('listing/list.html.twig', [
            'pagination' => $pagination,
            'filters' => $filters,
        ]);
    }

    /**
     * Génère un listing filtré et exporté en PDF ou Excel.
     */
    #[Route('/export/{format}', name: 'listing_export', requirements: ['format' => 'pdf|excel'])]
    public function export(
        ExportService $exportService,
        string $format,
        EntrepriseRepository $entrepriseRepo,
        Request $request
    ): Response {
        // Récupération des filtres pour l'exportation
        $criteria = [
            'nom' => $request->query->get('name'),
            'secteurActivite' => $request->query->get('sector'),
            'tailleEntreprise' => $request->query->get('size'),
            'dateCreation' => $request->query->get('date'),
        ];

        try {
            // Recherche des entreprises filtrées
            $query = $entrepriseRepo->searchEntreprises($criteria);
            $entreprises = $query->getResult();

            return $exportService->generateExport($entreprises, $format);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l’export des données.');
            return $this->redirectToRoute('listing_index');
        }
    }
}
