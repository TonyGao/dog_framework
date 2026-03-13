<?php

namespace App\Command;

use App\Entity\Organization\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:debug-login',
    description: 'Debug login for a user',
)]
class DebugLoginCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('identifier', InputArgument::REQUIRED, 'User identifier (username/email/employeeNo)');
        $this->addArgument('password', InputArgument::OPTIONAL, 'Password to test', 'password123');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $identifier = $input->getArgument('identifier');
        $password = $input->getArgument('password');

        // Try to find by username first, then email, then employeeNo manually to see what's what
        $repo = $this->entityManager->getRepository(Employee::class);
        
        $user = $repo->loadUserByIdentifier($identifier);

        if (!$user) {
            $io->error("User not found via loadUserByIdentifier('$identifier')");
            return Command::FAILURE;
        }

        $io->success(sprintf("Found user: %s (ID: %s)", $user->getName(), $user->getId()));
        $io->text("Stored Hash: " . $user->getPassword());

        $isValid = $this->passwordHasher->isPasswordValid($user, $password);

        if ($isValid) {
            $io->success("Password '$password' is VALID.");
        } else {
            $io->error("Password '$password' is INVALID.");
            
            // Try to see what the hash of the provided password would look like
            $newHash = $this->passwordHasher->hashPassword($user, $password);
            $io->text("Expected Hash for '$password': " . $newHash);
        }

        return Command::SUCCESS;
    }
}
