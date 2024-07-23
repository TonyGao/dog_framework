<?php

namespace App\Service\Entity;

class MigrationService
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function executeMigrationsDiff()
    {
        return $this->executeCommand('doctrine:migrations:diff');
    }

    public function executeMigrationsMigrate()
    {
        return $this->executeCommand('doctrine:migrations:migrate --no-interaction');
    }

    private function executeCommand(string $command)
    {
        $fullCommand = sprintf('php %s/bin/console %s 2>&1', $this->projectDir, $command);
        $output = [];
        $returnVar = 0;

        exec($fullCommand, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorOutput = implode("\n", $output);
            throw new \Exception(sprintf('Error executing %s: %s', $command, $errorOutput));
        }

        return implode("\n", $output);
    }
}
