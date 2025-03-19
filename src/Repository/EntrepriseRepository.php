<?php

namespace App\Repository;

use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreprise::class);
    }

    /**
     * Recherche avancée des entreprises en fonction des critères.
     */
    public function searchEntreprises(array $criteria)
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($criteria['nom'])) {
            $qb->andWhere('e.raisonSociale LIKE :nom')
                ->setParameter('nom', '%'.$criteria['nom'].'%');
        }

        if (!empty($criteria['secteurActivite'])) {
            $qb->andWhere('e.secteurActivite = :secteur')
                ->setParameter('secteur', $criteria['secteurActivite']);
        }

        if (!empty($criteria['tailleEntreprise'])) {
            $qb->andWhere('e.tailleEntreprise = :taille')
                ->setParameter('taille', $criteria['tailleEntreprise']);
        }

        if (!empty($criteria['dateCreation'])) {
            try {
                $date = new \DateTime($criteria['dateCreation']);
                $qb->andWhere('e.dateCreation >= :date')
                   ->setParameter('date', $date);
            } catch (\Exception $e) {
                // Ne rien faire si la date est invalide
            }
        }

        return $qb->getQuery();
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
