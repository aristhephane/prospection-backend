<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\WorkflowEvents;
use App\Entity\FicheEntreprise;

/**
 * Ce subscriber intercepte les transitions du workflow de prospection
 * pour mettre à jour le champ "statut" de l'entité FicheEntreprise.
 * La logique ci-dessous reprend strictement les noms et méthodes existants.
 */
class ProspectionWorkflowSubscriber implements EventSubscriberInterface
{
  public static function getSubscribedEvents(): array
  {
    // On écoute l’événement de transition afin d'intervenir à chaque changement d’état.
    return [
      WorkflowEvents::TRANSITION => 'onTransition',
    ];
  }

  public function onTransition(Event $event): void
  {
    $fiche = $event->getSubject();
    if (!$fiche instanceof FicheEntreprise) {
      return;
    }

    $transitionName = $event->getTransition()->getName();

    // Mise à jour de l'état "statut" en fonction de la transition appliquée.
    switch ($transitionName) {
      case 'demarrer':
        // Transition de "nouveau" vers "en_cours"
        $fiche->setStatut('en_cours');
        break;
      case 'valider':
        // Transition de "en_cours" vers "validé"
        $fiche->setStatut('validé');
        break;
      case 'archiver':
        // Transition de "validé" vers "archivé"
        $fiche->setStatut('archivé');
        break;
    }
  }
}
