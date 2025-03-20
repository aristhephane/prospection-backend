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
    // On écoute l'événement de transition afin d'intervenir à chaque changement d'état.
    return [
      WorkflowEvents::TRANSITION => 'onTransition',
      WorkflowEvents::ENTERED => 'onEntered',
      WorkflowEvents::COMPLETED => 'onCompleted',
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
      case 'contacter':
        $fiche->setStatut('contacté');
        break;
      case 'planifier':
        $fiche->setStatut('rendez_vous');
        break;
      case 'conclure':
        $fiche->setStatut('conclu');
        break;
      case 'rejeter':
        $fiche->setStatut('rejeté');
        break;
      // Ajoutez d'autres transitions selon votre workflow
    }
  }

  public function onEntered(Event $event): void
  {
    $fiche = $event->getSubject();
    if (!$fiche instanceof FicheEntreprise) {
      return;
    }

    // Mise à jour de la date de dernière modification
    $fiche->setUpdatedAt(new \DateTime());
  }

  public function onCompleted(Event $event): void
  {
    $fiche = $event->getSubject();
    if (!$fiche instanceof FicheEntreprise) {
      return;
    }

    // Actions à effectuer une fois que le workflow est complété
    if ($fiche->getStatut() === 'conclu') {
      // Par exemple, envoyer une notification ou créer une nouvelle tâche
    }
  }
}
