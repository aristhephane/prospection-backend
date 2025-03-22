<?php

namespace App\Repository;

use App\Entity\FicheEntreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FicheEntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheEntreprise::class);
    }

    public function save(FicheEntreprise $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FicheEntreprise $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countByStatus(): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('f.statut, COUNT(f.id) as count')
            ->groupBy('f.statut');

        $result = $qb->getQuery()->getResult();
        $counts = [];

        foreach ($result as $row) {
            $counts[$row['statut']] = (int) $row['count'];
        }

        return $counts;
    }

    public function countByMonth(\DateTime $dateDebut): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT 
                DATE_FORMAT(date_creation, "%Y-%m") as month_year, 
                COUNT(id) as count 
            FROM fiche_entreprise 
            WHERE date_creation >= :dateDebut 
            GROUP BY month_year 
            ORDER BY month_year ASC
        ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['dateDebut' => $dateDebut->format('Y-m-d')]);

        $counts = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $counts[$row['month_year']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Recherche avancée des fiches entreprises en fonction des critères.
     */
    public function searchFiches(array $criteria)
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.entreprise', 'e') // Jointure avec entreprise
            ->addSelect('e')
            ->leftJoin('f.creePar', 'u') // Jointure avec l'utilisateur qui a créé la fiche
            ->addSelect('u');

        if (!empty($criteria['nom'])) {
            $qb->andWhere('e.raisonSociale LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        if (!empty($criteria['secteurActivite'])) {
            $qb->andWhere('e.secteurActivite = :secteur')
                ->setParameter('secteur', $criteria['secteurActivite']);
        }

        if (!empty($criteria['dateCreation'])) {
            try {
                $date = new \DateTime($criteria['dateCreation']);
                $qb->andWhere('f.dateCreation >= :date')
                    ->setParameter('date', $date);
            } catch (\Exception $e) {
                // Ne rien faire si la date est invalide
            }
        }

        if (!empty($criteria['commentaires'])) {
            $qb->andWhere('f.commentaires LIKE :comment')
                ->setParameter('comment', '%' . $criteria['commentaires'] . '%');
        }

        if (!empty($criteria['utilisateur'])) {
            $qb->andWhere('u.email = :user')
                ->setParameter('user', $criteria['utilisateur']);
        }

        return $qb->getQuery();
    }
}
