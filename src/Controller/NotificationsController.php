<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;
    private NotificationService $notificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        NotificationService $notificationService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Liste les notifications de l'utilisateur courant
     */
    #[Route('/', name: 'notifications_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        $notifications = $this->notificationRepository->findBy(
            ['utilisateur' => $user],
            ['createdAt' => 'DESC']
        );

        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id' => $notification->getId(),
                'titre' => $notification->getTitre(),
                'contenu' => $notification->getContenu(),
                'type' => $notification->getType(),
                'date' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                'isRead' => $notification->isRead(),
            ];
        }

        return $this->json([
            'success' => true,
            'notifications' => $data
        ]);
    }

    /**
     * Marque une notification comme lue
     */
    #[Route('/{id}/mark-read', name: 'notification_mark_read', methods: ['POST'])]
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Vérifier si la notification appartient à l'utilisateur courant
        if ($notification->getUtilisateur() !== $this->getUser()) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à modifier cette notification.'
            ], Response::HTTP_FORBIDDEN);
        }

        $notification->setRead(true);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Notification marquée comme lue.'
        ]);
    }

    /**
     * Supprime une notification
     */
    #[Route('/{id}', name: 'notification_delete', methods: ['DELETE'])]
    public function delete(Notification $notification): JsonResponse
    {
        // Vérifier si la notification appartient à l'utilisateur courant
        if ($notification->getUtilisateur() !== $this->getUser()) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette notification.'
            ], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($notification);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Notification supprimée avec succès.'
        ]);
    }
}