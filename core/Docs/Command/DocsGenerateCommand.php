<?php

declare(strict_types=1);

namespace OOPress\Docs\Command;

use OOPress\Docs\DocGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * DocsGenerateCommand — Generates documentation.
 * 
 * @api
 */
class DocsGenerateCommand extends Command
{
    protected static $defaultName = 'docs:generate';
    
    public function __construct(
        private readonly DocGenerator $docGenerator,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Generate documentation from code and markdown')
            ->setHelp('This command generates API documentation and user guides.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('OOPress Documentation Generator');
        
        $io->section('Generating documentation...');
        
        $result = $this->docGenerator->generate();
        
        if ($result->isSuccess()) {
            $io->success($result->getSummary());
            $io->writeln(sprintf('Generated %d files in %.2f seconds', $result->getGeneratedCount(), $result->getDuration()));
            
            return Command::SUCCESS;
        }
        
        $io->error('Documentation generation failed');
        foreach ($result->getErrors() as $error) {
            $io->error($error);
        }
        
        return Command::FAILURE;
    }
}