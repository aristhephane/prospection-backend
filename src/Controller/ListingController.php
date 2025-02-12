<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\EntrepriseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ExportService;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/listings')]
class ListingController extends AbstractController
{
    /**
     * Génère un listing global en PDF/Excel.
     */
    #[Route('/export/{format}', name: 'listing_export', requirements: ['format' => 'pdf|excel'])]
    public function export(ExportService $exportService, string $format, EntrepriseRepository $entrepriseRepo, Request $request): Response
    {
        // Récupération des filtres
        $criteria = [
            'secteurActivite' => $request->query->get('sector'),
            'tailleEntreprise' => $request->query->get('size'),
            'nom' => $request->query->get('name'),
            'dateCreation' => $request->query->get('date'),
        ];

        $query = $entrepriseRepo->searchEntreprises($criteria);
        $entreprises = $query->getResult();

        return $exportService->generateExport($entreprises, $format);
    }

    /**
     * Génère un listing filtré par plusieurs critères.
     */
    #[Route('/filtre', name: 'listing_filtered')]
    public function listingFiltered(
        Request $request,
        EntrepriseRepository $entrepriseRepo,
        PaginatorInterface $paginator
    ): Response {
        // Récupération des filtres
        $criteria = [
            'secteurActivite' => $request->query->get('sector'),
            'tailleEntreprise' => $request->query->get('size'),
            'nom' => $request->query->get('name'),
            'dateCreation' => $request->query->get('date'),
        ];

        // Recherche des entreprises filtrées
        $query = $entrepriseRepo->searchEntreprises($criteria);

        // Pagination des résultats
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20 // Nombre d'éléments par page
        );

        return $this->render('listing/list.html.twig', [
            'pagination' => $pagination,
            'filters' => $criteria,
        ]);
    }
}