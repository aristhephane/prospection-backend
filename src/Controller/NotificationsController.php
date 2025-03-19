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
     * Affiche la liste des notifications non lues de l'utilisateur connecté.
     */
    #[Route('/', name: 'notifications_index', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        try {
            $notifications = $notificationRepository->findUnreadNotifications($user);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement des notifications.');
            $notifications = [];
        }

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Marque une notification comme lue.
     */
    #[Route('/marquer-lue/{id}', name: 'notification_mark_read', methods: ['POST'])]
    public function markAsRead(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || $notification->getUser() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette notification.');
            return $this->redirectToRoute('notifications_index');
        }

        try {
            $notification->setRead(true);
            $entityManager->flush();
            $this->addFlash('success', 'Notification marquée comme lue.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour de la notification.');
        }

        return $this->redirectToRoute('notifications_index');
    }

    /**
     * Supprime une notification.
     */
    #[Route('/supprimer/{id}', name: 'notification_delete', methods: ['POST'])]
    public function delete(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || $notification->getUser() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette notification.');
            return $this->redirectToRoute('notifications_index');
        }

        try {
            $entityManager->remove($notification);
            $entityManager->flush();
            $this->addFlash('success', 'Notification supprimée.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression de la notification.');
        }

        return $this->redirectToRoute('notifications_index');
    }
}