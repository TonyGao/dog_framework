<?php

namespace App\Command;

use App\Entity\Storage\StorageConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ef:init-storage-config',
    description: 'Initialize default storage configuration',
)]
class EfInitStorageConfigCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repo = $this->em->getRepository(StorageConfig::class);

        // Check if "local" config exists
        $localConfig = $repo->findOneBy(['name' => 'local']);

        if (!$localConfig) {
            $localConfig = new StorageConfig();
            $localConfig->setName('local');
            $localConfig->setAdapterType('local');
            $localConfig->setIsDefault(true);
            $localConfig->setConfig(['directory' => 'uploads']);
            
            $this->em->persist($localConfig);
            $io->success('Created default "local" storage configuration.');
        } else {
            $io->note('"local" storage configuration already exists.');
        }

        // Check if "s3" config exists (optional, maybe just create local for now)
        // But usually we might want an example S3 config, disabled by default?
        // Let's stick to local first as it's essential.

        $this->em->flush();

        return Command::SUCCESS;
    }
}
