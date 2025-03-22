<?php

namespace App\Controller\Api;

use App\Repository\FicheEntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/dashboard')]
class DashboardApiController extends AbstractController
{
  public function __construct(
    private FicheEntrepriseRepository $ficheRepository,
    private SerializerInterface $serializer
  ) {
  }

  #[Route('/statistics', name: 'api_dashboard_statistics', methods: ['GET'])]
  public function getStatistics(): Response
  {
    try {
      // Total des prospections
      $totalProspections = $this->ficheRepository->count([]);

      // Prospections par statut
      $prospectionsParStatut = $this->ficheRepository->countByStatus();

      // Dernières prospections
      $dernieresProspections = $this->ficheRepository->findBy([], ['dateCreation' => 'DESC'], 5);

      // Statistiques par mois (6 derniers mois)
      $dateDebut = new \DateTime();
      $dateDebut->modify('-6 months');
      $dateDebut->setDate($dateDebut->format('Y'), $dateDebut->format('m'), 1);
      $dateDebut->setTime(0, 0, 0);

      $prospectionsParMois = $this->ficheRepository->countByMonth($dateDebut);

      return $this->json([
        'success' => true,
        'totalProspections' => $totalProspections,
        'prospectionsParStatut' => $prospectionsParStatut,
        'dernieresProspections' => json_decode($this->serializer->serialize($dernieresProspections, 'json')),
        'prospectionsParMois' => $prospectionsParMois,
        'prospectionsEnCours' => $this->ficheRepository->count(['statut' => ['nouveau', 'en_contact', 'rendez_vous', 'proposition']]),
        'prospectionsConverties' => $this->ficheRepository->count(['statut' => 'client'])
      ]);
    } catch (\Exception $e) {
      return $this->json([
        'success' => false,
        'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  #[Route('/prospection', name: 'api_dashboard_prospection', methods: ['GET'])]
  public function getProspection(): Response
  {
    try {
      $dateLimite = new \DateTime('-7 days');
      $fichesRecents = $this->ficheRepository->createQueryBuilder('f')
        ->where('f.dateCreation >= :dateLimite')
        ->setParameter('dateLimite', $dateLimite)
        ->orderBy('f.dateCreation', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();

      return $this->json([
        'success' => true,
        'fichesRecents' => json_decode($this->serializer->serialize($fichesRecents, 'json'))
      ]);
    } catch (\Exception $e) {
      return $this->json([
        'success' => false,
        'message' => 'Erreur lors de la récupération des données de prospection: ' . $e->getMessage()
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
