<?php

namespace App\Command;

use App\Service\Utils\AlimtTranslationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'ef:translate',
  description: '通用翻译功能',
)]
class EfTranslateCommand extends Command
{
    protected static $defaultName = 'ef:translate';

    private $translationService;

    public function __construct(AlimtTranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Translate text using Alimt')
            ->addArgument('sourceText', InputArgument::REQUIRED, 'Text to translate')
            ->addArgument('sourceLanguage', InputArgument::OPTIONAL, 'Source language code', 'zh')
            ->addArgument('targetLanguage', InputArgument::OPTIONAL, 'Target language code', 'en');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceText = $input->getArgument('sourceText');
        $sourceLanguage = $input->getArgument('sourceLanguage');
        $targetLanguage = $input->getArgument('targetLanguage');
    
        try {
            $translatedText = $this->translationService->translate($sourceText, $sourceLanguage, $targetLanguage);
            $output->writeln('Translation: ' . $translatedText);
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }
    
        return Command::SUCCESS;
    }    
}
