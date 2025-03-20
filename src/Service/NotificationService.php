<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class NotificationService
{
  private EntityManagerInterface $entityManager;
  private Security $security;

  public function __construct(EntityManagerInterface $entityManager, Security $security)
  {
    $this->entityManager = $entityManager;
    $this->security = $security;
  }

  /**
   * Crée une nouvelle notification pour un utilisateur
   */
  public function createNotification(Utilisateur $utilisateur, string $titre, string $contenu, string $type = 'info'): Notification
  {
    $notification = new Notification();
    $notification->setTitre($titre);
    $notification->setContenu($contenu);
    $notification->setType($type);
    $notification->setUtilisateur($utilisateur);
    $notification->setRead(false);

    $this->entityManager->persist($notification);
    $this->entityManager->flush();

    return $notification;
  }

  /**
   * Marque une notification comme lue
   */
  public function markAsRead(Notification $notification): Notification
  {
    $notification->setRead(true);
    $this->entityManager->flush();

    return $notification;
  }

  /**
   * Récupère les notifications non lues de l'utilisateur courant
   */
  public function getUnreadNotifications(): array
  {
    $user = $this->security->getUser();
    if (!$user) {
      return [];
    }

    return $this->entityManager->getRepository(Notification::class)
      ->findBy([
        'utilisateur' => $user,
        'isRead' => false
      ], ['createdAt' => 'DESC']);
  }

  /**
   * Récupère toutes les notifications de l'utilisateur courant
   */
  public function getAllNotifications(): array
  {
    $user = $this->security->getUser();
    if (!$user) {
      return [];
    }

    return $this->entityManager->getRepository(Notification::class)
      ->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);
  }
}
