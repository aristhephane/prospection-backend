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
                ->setParameter('nom', '%'.$criteria['nom'].'%');
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
                ->setParameter('comment', '%'.$criteria['commentaires'].'%');
        }

        if (!empty($criteria['utilisateur'])) {
            $qb->andWhere('u.email = :user')
                ->setParameter('user', $criteria['utilisateur']);
        }

        return $qb->getQuery();
    }
}
