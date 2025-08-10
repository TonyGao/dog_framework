<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ef:have-break',
    description: 'Have a break, relax',
)]
class HaveBreakCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Displays a persistent notification on macOS every hour.');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {
            $this->sendNotification("该休息了！", "休息提醒", "请点击此通知以关闭");

            $output->writeln('Notification sent successfully.');

            // 等待一小时（3600秒）
            sleep(3600);
        }
    }

    private function sendNotification(string $message, string $title, string $subtitle)
    {
        $command = sprintf(
            'terminal-notifier -message "%s" -title "%s" -subtitle "%s" -timeout 0',
            addslashes($message),
            addslashes($title),
            addslashes($subtitle)
        );

        exec($command);
    }
}
