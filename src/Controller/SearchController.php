<?php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use App\Repository\FicheEntrepriseRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recherche')]
class SearchController extends AbstractController
{
    /**
     * Affiche la page de recherche avancée pour les entreprises et fiches.
     */
    #[Route('/', name: 'search_index')]
    public function index(
        Request $request, 
        EntrepriseRepository $entrepriseRepo, 
        FicheEntrepriseRepository $ficheRepo,
        PaginatorInterface $paginator
    ): Response {
        $searchTerm = $request->query->get('q', '');
        $sector = $request->query->get('sector', '');
        $size = $request->query->get('size', '');

        // Recherche des fiches
        $queryEntreprises = $entrepriseRepo->createQueryBuilder('e')
            ->where('e.raisonSociale LIKE :searchTerm OR e.secteurActivite LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%');

        if (!empty($sector)) {
            $queryEntreprises->andWhere('e.secteurActivite = :sector')
                ->setParameter('sector', $sector);
        }

        if (!empty($size)) {
            $queryEntreprises->andWhere('e.tailleEntreprise = :size')
                ->setParameter('size', $size);
        }

        $pagination = $paginator->paginate(
            $queryEntreprises->getQuery(),
            $request->query->getInt('page', 1),
            20 // Nombre d'éléments par page
        );

        return $this->render('search/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
        ]);
    }
}
