<?php

namespace App\Repository;

use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entreprise>
 */
class EntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreprise::class);
    }

    /**
     * Recherche des entreprises avec plusieurs critères.
     */
    public function searchEntreprises(array $criteria)
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($criteria['secteurActivite'])) {
            $qb->andWhere('e.secteurActivite = :sector')
               ->setParameter('sector', $criteria['secteurActivite']);
        }

        if (!empty($criteria['tailleEntreprise'])) {
            $qb->andWhere('e.tailleEntreprise = :size')
               ->setParameter('size', $criteria['tailleEntreprise']);
        }

        if (!empty($criteria['nom'])) {
            $qb->andWhere('e.raisonSociale LIKE :name')
               ->setParameter('name', '%' . $criteria['nom'] . '%');
        }

        if (!empty($criteria['dateCreation'])) {
            $qb->andWhere('e.dateCreation >= :date')
               ->setParameter('date', new \DateTime($criteria['dateCreation']));
        }

        return $qb->orderBy('e.dateCreation', 'DESC')
                  ->getQuery();
    }

    //    /**
    //     * @return Entreprise[] Returns an array of Entreprise objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Entreprise
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
