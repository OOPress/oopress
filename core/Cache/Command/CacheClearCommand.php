<?php

declare(strict_types=1);

namespace OOPress\Cache\Command;

use OOPress\Cache\CacheManager;
use OOPress\Cache\PageCache;
use OOPress\Cache\BlockCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CacheClearCommand — Clears cache.
 * 
 * @api
 */
class CacheClearCommand extends Command
{
    protected static $defaultName = 'cache:clear';
    
    public function __construct(
        private readonly CacheManager $cache,
        private readonly PageCache $pageCache,
        private readonly BlockCache $blockCache,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Clear cache')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Clear all cache')
            ->addOption('page', 'p', InputOption::VALUE_NONE, 'Clear page cache only')
            ->addOption('block', 'b', InputOption::VALUE_NONE, 'Clear block cache only')
            ->addOption('tag', 't', InputOption::VALUE_REQUIRED, 'Clear cache by tag');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Clearing Cache');
        
        $clearAll = $input->getOption('all');
        $clearPage = $input->getOption('page');
        $clearBlock = $input->getOption('block');
        $tag = $input->getOption('tag');
        
        if ($tag) {
            $io->section(sprintf('Invalidating tag: %s', $tag));
            $this->cache->invalidateTag($tag);
            $io->success('Cache invalidated');
            return Command::SUCCESS;
        }
        
        if ($clearAll || (!$clearPage && !$clearBlock)) {
            $io->section('Clearing all cache');
            $this->cache->clear();
            $io->success('All cache cleared');
        } else {
            if ($clearPage) {
                $io->section('Clearing page cache');
                $this->pageCache->clear();
                $io->success('Page cache cleared');
            }
            
            if ($clearBlock) {
                $io->section('Clearing block cache');
                $this->blockCache->clear();
                $io->success('Block cache cleared');
            }
        }
        
        return Command::SUCCESS;
    }
}