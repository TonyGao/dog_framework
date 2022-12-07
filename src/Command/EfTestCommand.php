<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name:'ef:test',
    description:'Add a short description for your command',
)]
class EfTestCommand extends Command
{
    protected function configure(): void
    {
        $this
        // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Option description');
    }

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        if ($input->getOption('test')) {

            $response = $this->client->request(
                'GET',
                'https://www.163.com'
            );
            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            $contentType = $response->getHeaders();
            dump($contentType);
            $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

            return Command::SUCCESS;
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
