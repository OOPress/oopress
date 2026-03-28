<?php

declare(strict_types=1);

namespace OOPress\Search\Command;

use OOPress\Search\SearchManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * RebuildIndexCommand — Rebuilds search index.
 * 
 * @api
 */
class RebuildIndexCommand extends Command
{
    protected static $defaultName = 'search:rebuild';
    
    public function __construct(
        private readonly SearchManager $searchManager,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Rebuild the search index')
            ->setHelp('This command rebuilds the search index from all content.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        if (!$this->searchManager->isAvailable()) {
            $io->error('Search backend is not available.');
            return Command::FAILURE;
        }
        
        $io->title('Rebuilding Search Index');
        
        $io->section('Clearing existing index...');
        $this->searchManager->clear();
        
        $io->section('Rebuilding index...');
        $this->searchManager->rebuild();
        
        $stats = $this->searchManager->getStats();
        
        $io->success('Search index rebuilt successfully');
        $io->writeln(sprintf('Total documents indexed: %d', $stats['total_documents'] ?? 0));
        
        if (isset($stats['by_type'])) {
            $io->section('Index by type');
            $rows = array_map(fn($item) => [$item['type'], $item['count']], $stats['by_type']);
            $io->table(['Type', 'Count'], $rows);
        }
        
        return Command::SUCCESS;
    }
}