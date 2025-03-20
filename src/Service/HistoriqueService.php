<?php

namespace App\Service;

use App\Entity\HistoriqueModification;
use App\Entity\FicheEntreprise;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class HistoriqueService
{
  private EntityManagerInterface $entityManager;
  private Security $security;
  private UtilisateurRepository $utilisateurRepository;

  public function __construct(EntityManagerInterface $entityManager, Security $security, UtilisateurRepository $utilisateurRepository)
  {
    $this->entityManager = $entityManager;
    $this->security = $security;
    $this->utilisateurRepository = $utilisateurRepository;
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
