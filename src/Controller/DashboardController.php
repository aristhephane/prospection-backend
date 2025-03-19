<?php
// src/Controller/DashboardController.php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use App\Repository\FicheEntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    /**
     * Affiche la page d'accueil du tableau de bord avec une synthèse générale.
     */
    #[Route('/', name: 'dashboard_index', methods: ['GET'])]
    public function index(EntrepriseRepository $entrepriseRepo, FicheEntrepriseRepository $ficheRepo): Response
    {
        try {
            // Optimisation : Utilisation de COUNT() en base de données
            $nombreEntreprises = $entrepriseRepo->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $nombreFiches = $ficheRepo->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->getQuery()
                ->getSingleScalarResult();
            
            // Récupération des dernières entreprises ajoutées
            $dernieresEntreprises = $entrepriseRepo->createQueryBuilder('e')
                ->orderBy('e.id', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();

            // Tableau de statistiques
            $stats = [
                'nombreEntreprises' => $nombreEntreprises,
                'nombreFiches' => $nombreFiches,
                'dernieresEntreprises' => $dernieresEntreprises,
            ];

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement des statistiques.');
            $stats = [];
        }

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * Affiche des statistiques détaillées.
     */
    #[Route('/statistiques', name: 'dashboard_statistiques', methods: ['GET'])]
    public function statistiques(EntrepriseRepository $entrepriseRepo, FicheEntrepriseRepository $ficheRepo): Response
    {
        try {
            $entreprises = $entrepriseRepo->findAll();
            $secteurs = [];
            foreach ($entreprises as $entreprise) {
                $secteur = $entreprise->getSecteurActivite();
                if (!isset($secteurs[$secteur])) {
                    $secteurs[$secteur] = 0;
                }
                $secteurs[$secteur]++;
            }

            // Récupération du nombre de fiches créées par mois sur les 12 derniers mois
            $fichesParMois = $ficheRepo->createQueryBuilder('f')
                ->select('MONTH(f.dateCreation) as mois, COUNT(f.id) as total')
                ->where('f.dateCreation >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime('-12 months'))
                ->groupBy('mois')
                ->orderBy('mois', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement des statistiques détaillées.');
            $secteurs = [];
            $fichesParMois = [];
        }

        return $this->render('dashboard/statistiques.html.twig', [
            'secteurs' => $secteurs,
            'fichesParMois' => $fichesParMois,
        ]);
    }

    /**
     * Affiche les fiches de prospection récentes.
     */
    #[Route('/prospection', name: 'dashboard_prospection', methods: ['GET'])]
    public function prospection(FicheEntrepriseRepository $ficheRepo): Response
    {
        try {
            $dateLimite = new \DateTime('-7 days');
            $fichesRecents = $ficheRepo->createQueryBuilder('f')
                ->where('f.dateCreation >= :dateLimite')
                ->setParameter('dateLimite', $dateLimite)
                ->orderBy('f.dateCreation', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement des données de prospection.');
            $fichesRecents = [];
        }

        return $this->render('dashboard/prospection.html.twig', [
            'fichesRecents' => $fichesRecents,
        ]);
    }
}
