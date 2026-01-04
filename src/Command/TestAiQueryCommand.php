<?php

namespace App\Command;

use App\Service\AI\AgentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ai:test-query',
    description: 'Test the AI Natural Language Query Agent',
)]
class TestAiQueryCommand extends Command
{
    public function __construct(
        private AgentManager $agentManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'The natural language query')
            ->addArgument('entity', InputArgument::OPTIONAL, 'The entity class', 'App\Entity\Organization\Position')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('query');
        $entity = $input->getArgument('entity');

        $io->title('AI Query Test');
        $io->text("Query: $query");
        $io->text("Entity: $entity");

        try {
            $io->section('Parsing...');
            $filters = $this->agentManager->parseQuery($query, $entity);

            $io->success('Result:');
            $io->write(json_encode($filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            $io->text($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
