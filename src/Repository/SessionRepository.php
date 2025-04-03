<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * Trouve toutes les sessions expirées
     * @return Session[] Returns an array of expired Session objects
     */
    public function findExpiredSessions(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateExpiration < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Trouve les sessions actives d'un utilisateur
     */
    public function findActiveSessionsByUser(int $userId): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('s')
            ->andWhere('s.utilisateur = :userId')
            ->andWhere('s.dateExpiration > :now')
            ->setParameter('userId', $userId)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Session[] Returns an array of Session objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Session
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
