<?php

namespace App\Controller;

use App\Entity\FicheEntreprise;
use App\Repository\FicheEntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/prospection')]
class ProspectionController extends AbstractController
{
  public function __construct(
    private FicheEntrepriseRepository $repository,
    private WorkflowInterface $prospectionWorkflow,
    private SerializerInterface $serializer
  ) {
  }

  #[Route('/{id}/apply-transition/{transition}', name: 'prospection_apply_transition', methods: ['POST'])]
  public function applyTransition(FicheEntreprise $fiche, string $transition): Response
  {
    try {
      if ($this->prospectionWorkflow->can($fiche, $transition)) {
        $this->prospectionWorkflow->apply($fiche, $transition);
        $this->repository->save($fiche, true);

        return $this->json([
          'success' => true,
          'message' => 'Transition appliquée avec succès',
          'fiche' => json_decode($this->serializer->serialize($fiche, 'json'))
        ]);
      }

      return $this->json([
        'success' => false,
        'message' => 'La transition n\'est pas possible dans l\'état actuel'
      ], Response::HTTP_BAD_REQUEST);
    } catch (\Exception $e) {
      return $this->json([
        'success' => false,
        'message' => $e->getMessage()
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  #[Route('/{id}/possible-transitions', name: 'prospection_possible_transitions', methods: ['GET'])]
  public function getPossibleTransitions(FicheEntreprise $fiche): Response
  {
    $transitions = array_map(
      fn($transition) => $transition->getName(),
      $this->prospectionWorkflow->getEnabledTransitions($fiche)
    );

    return $this->json([
      'success' => true,
      'transitions' => $transitions
    ]);
  }
}