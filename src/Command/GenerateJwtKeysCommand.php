<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
  name: 'app:generate-jwt-keys',
  description: 'Generates JWT public and private keys'
)]
class GenerateJwtKeysCommand extends Command
{
  public function __construct(
    private ParameterBagInterface $params
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    $projectDir = $this->params->get('kernel.project_dir');
    $jwtDir = $projectDir . '/config/jwt';
    $privateKeyPath = $jwtDir . '/private.pem';
    $publicKeyPath = $jwtDir . '/public.pem';
    $passphrase = $_ENV['JWT_PASSPHRASE'] ?? 'd6128fe74303eefb28994e3efe35e78b970e451baa28e61edc6594d4cc33a137';

    // Create JWT directory if it doesn't exist
    if (!is_dir($jwtDir)) {
      $io->note("Creating JWT directory: {$jwtDir}");
      mkdir($jwtDir, 0755, true);
    }

    // Delete existing keys if they exist
    if (file_exists($privateKeyPath)) {
      $io->note("Removing existing private key");
      unlink($privateKeyPath);
    }

    if (file_exists($publicKeyPath)) {
      $io->note("Removing existing public key");
      unlink($publicKeyPath);
    }

    // Generate private key
    $io->section("Generating private key");
    $privateKey = openssl_pkey_new([
      'private_key_bits' => 4096,
      'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    // Extract private key and write to file
    openssl_pkey_export($privateKey, $privateKeyPem, $passphrase);
    file_put_contents($privateKeyPath, $privateKeyPem);
    chmod($privateKeyPath, 0600);

    // Extract public key and write to file
    $keyDetails = openssl_pkey_get_details($privateKey);
    file_put_contents($publicKeyPath, $keyDetails['key']);
    chmod($publicKeyPath, 0644);

    $io->success([
      "JWT keys generated successfully:",
      "Private key: {$privateKeyPath}",
      "Public key: {$publicKeyPath}"
    ]);

    return Command::SUCCESS;
  }
}
