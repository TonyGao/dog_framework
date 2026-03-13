<?php

namespace App\Command;

use App\Entity\System\SystemUser;
use App\Repository\System\SystemUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:init-officers',
    description: 'Initialize the three security officers (System Admin, Security Admin, Auditor)',
)]
class InitOfficersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private SystemUserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $officers = [
            'sys_admin' => ['role' => 'ROLE_SYS_ADMIN', 'name' => 'System Administrator'],
            'sec_admin' => ['role' => 'ROLE_SEC_ADMIN', 'name' => 'Security Administrator'],
            'auditor'   => ['role' => 'ROLE_AUDITOR', 'name' => 'Security Auditor'],
        ];

        foreach ($officers as $username => $data) {
            $user = $this->userRepository->findOneBy(['username' => $username]);
            
            if ($user) {
                $io->note(sprintf('User "%s" already exists.', $username));
                continue;
            }

            $user = new SystemUser();
            $user->setUsername($username);
            $user->setRoles([$data['role']]);
            $user->setIsActive(true);
            
            // Default password
            $password = 'Officer@123'; 
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $io->success(sprintf('Created user "%s" with role %s. Password: %s', $username, $data['role'], $password));
        }

        $this->entityManager->flush();

        $io->success('Three officers initialization completed.');

        return Command::SUCCESS;
    }
}
