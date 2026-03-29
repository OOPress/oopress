<?php

declare(strict_types=1);

namespace OOPress\Cache\Command;

use OOPress\Cache\CacheManager;
use OOPress\Event\HookDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CacheWarmCommand — Warms up cache.
 * 
 * @api
 */
class CacheWarmCommand extends Command
{
    protected static $defaultName = 'cache:warm';
    
    public function __construct(
        private readonly CacheManager $cache,
        private readonly HookDispatcher $hookDispatcher,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Warm up cache')
            ->setHelp('This command warms up the cache by preloading common content.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Warming Up Cache');
        
        $io->section('Dispatching cache warmup event');
        
        $event = new \OOPress\Cache\Event\CacheWarmupEvent();
        $this->hookDispatcher->dispatch($event, 'cache.warmup');
        
        $io->success('Cache warmed up');
        
        return Command::SUCCESS;
    }
}