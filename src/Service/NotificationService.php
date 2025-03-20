<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
  private EntityManagerInterface $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  /**
   * Crée une nouvelle notification pour un utilisateur
   */
  public function creerNotification(Utilisateur $utilisateur, string $titre, string $contenu, string $type = 'info'): Notification
  {
    $notification = new Notification();
    $notification->setUser($utilisateur);
    $notification->setTitre($titre);
    $notification->setContenu($contenu);
    $notification->setType($type);
    $notification->setCreatedAt(new \DateTime());
    $notification->setRead(false);

    $this->entityManager->persist($notification);
    $this->entityManager->flush();

    return $notification;
  }

  /**
   * Marque une notification comme lue
   */
  public function marquerCommeLue(Notification $notification): void
  {
    $notification->setRead(true);
    $this->entityManager->flush();
  }

  /**
   * Récupère les notifications non lues d'un utilisateur
   * @return Notification[]
   */
  public function getNotificationsNonLues(Utilisateur $utilisateur): array
  {
    return $this->entityManager->getRepository(Notification::class)
      ->findBy(['user' => $utilisateur, 'read' => false], ['createdAt' => 'DESC']);
  }

  /**
   * Envoie une notification à tous les administrateurs
   * @return Notification[]
   */
  public function notifierAdministrateurs(string $titre, string $contenu, string $type = 'info'): array
  {
    $admins = $this->entityManager->getRepository(Utilisateur::class)
      ->createQueryBuilder('u')
      ->join('u.roles', 'r')
      ->where('r.nom = :role')
      ->setParameter('role', 'ROLE_ADMIN')
      ->getQuery()
      ->getResult();

    $notifications = [];
    foreach ($admins as $admin) {
      $notifications[] = $this->creerNotification($admin, $titre, $contenu, $type);
    }

    return $notifications;
  }
}
