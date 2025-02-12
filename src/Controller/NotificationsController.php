<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotificationsController extends AbstractController
{
    /**
     * Affiche la liste des notifications pour l'utilisateur connecté.
     */
    #[Route('/', name: 'notifications_index')]
    public function index(NotificationRepository $notificationRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $notifications = $notificationRepo->findUnreadNotifications($user);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Marque une notification comme lue.
     */
    #[Route('/marquer-lue/{id}', name: 'notification_mark_read')]
    public function markAsRead(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        $notification->setRead(true);
        $entityManager->flush();

        return $this->redirectToRoute('notifications_index');
    }

    /**
     * Supprime une notification.
     */
    #[Route('/supprimer/{id}', name: 'notification_delete')]
    public function delete(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($notification);
        $entityManager->flush();

        return $this->redirectToRoute('notifications_index');
    }
}
