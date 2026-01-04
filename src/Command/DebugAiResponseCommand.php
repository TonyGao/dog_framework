<?php

namespace App\Command;

use App\Service\AI\Agent\NaturalLanguageQueryAgent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:debug-ai-response',
    description: 'Debug AI Response Structure',
)]
class DebugAiResponseCommand extends Command
{
    public function __construct(
        private NaturalLanguageQueryAgent $agent
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $text = "岗位是总经理，且状态是可用的";
        $entityClass = 'App\Entity\Organization\Position';
        $currentFilters = [];

        $io->title('Debugging AI Response Structure');

        // We can't easily hook into the raw response without modifying the class or using reflection/logging
        // But we can inspect the final return value of parseQuery

        $result = $this->agent->parseQuery($text, $entityClass, $currentFilters);

        $io->section('Final Result from parseQuery');
        $io->text(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (isset($result['filters']) && isset($result['filters']['filters'])) {
             $io->error("Double nesting detected!");
        } else {
             $io->success("Structure looks correct (single level filters).");
        }

        return Command::SUCCESS;
    }
}
