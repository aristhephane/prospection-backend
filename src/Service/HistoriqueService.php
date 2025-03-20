<?php

namespace App\Service;

use App\Entity\HistoriqueModification;
use App\Entity\FicheEntreprise;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class HistoriqueService
{
  private EntityManagerInterface $entityManager;
  private Security $security;

  public function __construct(EntityManagerInterface $entityManager, Security $security)
  {
    $this->entityManager = $entityManager;
    $this->security = $security;
  }

  /**
   * Enregistre une modification dans l'historique
   */
  public function enregistrerModification(FicheEntreprise $fiche, string $details = null): HistoriqueModification
  {
    $utilisateur = $this->security->getUser();

    $historique = new HistoriqueModification();
    $historique->setDateModification(new \DateTime());
    $historique->setDetailsModification(
      $details ?? 'Modification effectuée par ' . $utilisateur->getUserIdentifier()
    );
    $historique->setFicheEntreprise($fiche);
    $historique->setUtilisateur($utilisateur);

    $this->entityManager->persist($historique);

    return $historique;
  }

  /**
   * Récupère l'historique des modifications pour une fiche
   */
  public function getHistoriqueFiche(FicheEntreprise $fiche): array
  {
    return $this->entityManager->getRepository(HistoriqueModification::class)
      ->findBy(['ficheEntreprise' => $fiche], ['dateModification' => 'DESC']);
  }
}
