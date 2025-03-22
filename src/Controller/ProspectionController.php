<?php

namespace App\Controller;

use App\Entity\FicheEntreprise;
use App\Repository\FicheEntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

  #[Route('', name: 'prospection_index', methods: ['GET'])]
  public function index(): Response
  {
    $fiches = $this->repository->findBy([], ['dateCreation' => 'DESC']);

    return $this->json([
      'success' => true,
      'data' => json_decode($this->serializer->serialize($fiches, 'json'))
    ]);
  }

  #[Route('/{id}', name: 'prospection_show', methods: ['GET'])]
  public function show(FicheEntreprise $fiche): Response
  {
    return $this->json([
      'success' => true,
      'fiche' => json_decode($this->serializer->serialize($fiche, 'json'))
    ]);
  }

  #[Route('', name: 'prospection_create', methods: ['POST'])]
  public function create(Request $request): Response
  {
    try {
      $data = json_decode($request->getContent(), true);

      $fiche = new FicheEntreprise();
      $fiche->setNom($data['nom']);
      $fiche->setEntreprise($data['entreprise']);
      $fiche->setEmail($data['email'] ?? null);
      $fiche->setTelephone($data['telephone'] ?? null);
      $fiche->setSecteur($data['secteur'] ?? null);
      $fiche->setNotes($data['notes'] ?? null);
      $fiche->setDateCreation(new \DateTime());
      $fiche->setDateModification(new \DateTime());
      $fiche->setStatut('nouveau');

      $this->repository->save($fiche, true);

      return $this->json([
        'success' => true,
        'message' => 'Fiche de prospection créée avec succès',
        'fiche' => json_decode($this->serializer->serialize($fiche, 'json'))
      ], Response::HTTP_CREATED);
    } catch (\Exception $e) {
      return $this->json([
        'success' => false,
        'message' => $e->getMessage()
      ], Response::HTTP_BAD_REQUEST);
    }
  }

  #[Route('/{id}', name: 'prospection_update', methods: ['PUT'])]
  public function update(Request $request, FicheEntreprise $fiche): Response
  {
    try {
      $data = json_decode($request->getContent(), true);

      if (isset($data['nom']))
        $fiche->setNom($data['nom']);
      if (isset($data['entreprise']))
        $fiche->setEntreprise($data['entreprise']);
      if (array_key_exists('email', $data))
        $fiche->setEmail($data['email']);
      if (array_key_exists('telephone', $data))
        $fiche->setTelephone($data['telephone']);
      if (array_key_exists('secteur', $data))
        $fiche->setSecteur($data['secteur']);
      if (array_key_exists('notes', $data))
        $fiche->setNotes($data['notes']);
      $fiche->setDateModification(new \DateTime());

      $this->repository->save($fiche, true);

      return $this->json([
        'success' => true,
        'message' => 'Fiche de prospection mise à jour avec succès',
        'fiche' => json_decode($this->serializer->serialize($fiche, 'json'))
      ]);
    } catch (\Exception $e) {
      return $this->json([
        'success' => false,
        'message' => $e->getMessage()
      ], Response::HTTP_BAD_REQUEST);
    }
  }

  #[Route('/{id}', name: 'prospection_delete', methods: ['DELETE'])]
  public function delete(FicheEntreprise $fiche): Response
  {
    try {
      $this->repository->remove($fiche, true);

      return $this->json([
        'success' => true,
        'message' => 'Fiche de prospection supprimée avec succès'
      ]);
    } catch (\Exception $e) {
      return $this->json([
        'success' => false,
        'message' => $e->getMessage()
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
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