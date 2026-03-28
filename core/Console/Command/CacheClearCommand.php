<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use OOPress\Path\PathResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CacheClearCommand — Clears all caches.
 * 
 * @api
 */
class CacheClearCommand extends Command
{
    protected static $defaultName = 'cache:clear';
    
    public function __construct(
        private readonly PathResolver $pathResolver,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Clear all caches')
            ->setHelp('This command clears all cached data including Twig templates, Doctrine metadata, and other caches.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Clearing OOPress Caches');
        
        $cachePaths = [
            $this->pathResolver->getVarPath() . '/cache/twig',
            $this->pathResolver->getVarPath() . '/cache/doctrine',
            $this->pathResolver->getVarPath() . '/cache/validation',
            $this->pathResolver->getVarPath() . '/cache/translations',
        ];
        
        $cleared = 0;
        
        foreach ($cachePaths as $path) {
            if (is_dir($path)) {
                $io->text(sprintf('Clearing: %s', $path));
                $this->recursiveDelete($path);
                mkdir($path, 0755, true);
                $cleared++;
            }
        }
        
        if ($cleared > 0) {
            $io->success(sprintf('Cleared %d cache directories', $cleared));
        } else {
            $io->info('No caches found to clear.');
        }
        
        return Command::SUCCESS;
    }
    
    private function recursiveDelete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        
        if (is_dir($path)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    rmdir($item->getPathname());
                } else {
                    unlink($item->getPathname());
                }
            }
            
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}