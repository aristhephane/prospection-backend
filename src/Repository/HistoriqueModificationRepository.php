<?php

namespace App\Repository;

use App\Entity\HistoriqueModification;
use App\Entity\Utilisateur;
use App\Entity\FicheEntreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriqueModification>
 */
class HistoriqueModificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueModification::class);
    }

    /**
     * Récupère l'historique des modifications récentes
     * @return HistoriqueModification[] Returns an array of HistoriqueModification objects
     */
    public function findRecentModifications(int $limit = 10): array
    {
        return $this->createQueryBuilder('h')
            ->orderBy('h.dateModification', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère l'historique des modifications faites par un utilisateur
     * @return HistoriqueModification[] Returns an array of HistoriqueModification objects
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('h.dateModification', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère l'historique des modifications pour une fiche entreprise
     * @return HistoriqueModification[] Returns an array of HistoriqueModification objects
     */
    public function findByFicheEntreprise(FicheEntreprise $fiche): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.ficheEntreprise = :fiche')
            ->setParameter('fiche', $fiche)
            ->orderBy('h.dateModification', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
