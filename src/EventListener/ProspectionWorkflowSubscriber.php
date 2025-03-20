<?php

namespace App\EventListener;

use App\Entity\FicheEntreprise;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\WorkflowEvents;
use Symfony\Component\Security\Core\Security;

class ProspectionWorkflowSubscriber implements EventSubscriberInterface
{
  private $security;

  public function __construct(Security $security)
  {
    $this->security = $security;
  }

  public static function getSubscribedEvents()
  {
    return [
      'workflow.prospection.completed.contacter' => 'onContactPris',
      'workflow.prospection.completed.signer_contrat' => 'onContratSigne',
      'workflow.prospection.guard.archiver' => 'guardArchiver',
    ];
  }

  public function onContactPris(Event $event)
  {
    /** @var FicheEntreprise $ficheEntreprise */
    $ficheEntreprise = $event->getSubject();
    $ficheEntreprise->setDateDernierContact(new \DateTime());

    // Autres actions à effectuer après un contact
  }

  public function onContratSigne(Event $event)
  {
    /** @var FicheEntreprise $ficheEntreprise */
    $ficheEntreprise = $event->getSubject();
    $ficheEntreprise->setDateSignature(new \DateTime());

    // Envoyer notification ou email de félicitations
  }

  public function guardArchiver(GuardEvent $event)
  {
    if (!$this->security->isGranted('ROLE_SUPERVISEUR')) {
      $event->setBlocked(true, 'Seuls les superviseurs peuvent archiver une fiche.');
    }
  }
}
