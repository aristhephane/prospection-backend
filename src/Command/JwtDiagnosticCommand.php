<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
  name: 'app:jwt-diagnostic',
  description: 'Vérifier la configuration JWT',
)]
class JwtDiagnosticCommand extends Command
{
  private $params;

  public function __construct(ParameterBagInterface $params)
  {
    parent::__construct();
    $this->params = $params;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $io->title('Diagnostic JWT');

    // Récupérer les paramètres JWT
    $secretKeyPath = $this->params->get('lexik_jwt_authentication.secret_key');
    $publicKeyPath = $this->params->get('lexik_jwt_authentication.public_key');
    $passphrase = $this->params->get('lexik_jwt_authentication.pass_phrase');

    // Vérifier l'existence des clés
    $secretKeyExists = file_exists($secretKeyPath);
    $publicKeyExists = file_exists($publicKeyPath);

    $io->section('Vérification de l\'existence des fichiers');
    $io->table(
      ['Fichier', 'Chemin', 'Existe', 'Permissions'],
      [
        ['Clé privée', $secretKeyPath, $secretKeyExists ? 'Oui' : 'Non', $secretKeyExists ? substr(sprintf('%o', fileperms($secretKeyPath)), -4) : 'N/A'],
        ['Clé publique', $publicKeyPath, $publicKeyExists ? 'Oui' : 'Non', $publicKeyExists ? substr(sprintf('%o', fileperms($publicKeyPath)), -4) : 'N/A'],
      ]
    );

    // Vérifier le contenu des clés
    if ($secretKeyExists) {
      $io->section('Test de la clé privée');
      try {
        $privateKey = openssl_pkey_get_private('file://' . $secretKeyPath, $passphrase);
        if ($privateKey === false) {
          $io->error("La clé privée est invalide ou la passphrase est incorrecte: " . openssl_error_string());
        } else {
          $io->success('La clé privée est valide et la passphrase est correcte');
          // Tester l'encodage/décodage
          $data = 'test';
          openssl_private_encrypt($data, $encrypted, $privateKey);

          $io->success('La clé privée peut être utilisée pour le chiffrement');
        }
      } catch (\Exception $e) {
        $io->error('Erreur lors du test de la clé privée: ' . $e->getMessage());
      }
    }

    // Afficher la configuration
    $io->section('Configuration JWT');
    $io->table(
      ['Paramètre', 'Valeur'],
      [
        ['JWT_SECRET_KEY', $secretKeyPath],
        ['JWT_PUBLIC_KEY', $publicKeyPath],
        ['JWT_PASSPHRASE', substr($passphrase, 0, 3) . '...' . substr($passphrase, -3)],
      ]
    );

    $io->success('Diagnostic terminé.');
    return Command::SUCCESS;
  }
}
