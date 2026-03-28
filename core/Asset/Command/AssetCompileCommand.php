<?php

declare(strict_types=1);

namespace OOPress\Asset\Command;

use OOPress\Asset\AssetManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AssetCompileCommand — Compiles all assets.
 * 
 * @api
 */
class AssetCompileCommand extends Command
{
    protected static $defaultName = 'asset:compile';
    
    public function __construct(
        private readonly AssetManager $assetManager,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Compile all assets (CSS, JS, fonts)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force recompilation even if not needed')
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and recompile automatically');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $watch = $input->getOption('watch');
        
        $io->title('OOPress Asset Compiler');
        
        if ($watch) {
            $io->note('Watch mode not yet implemented. Use --force to compile once.');
        }
        
        $io->section('Compiling assets...');
        
        $result = $this->assetManager->compileAssets($force);
        
        if ($result->isSuccess()) {
            $io->success('Assets compiled successfully');
            $io->writeln($result->getSummary());
            
            $errors = $this->assetManager->getErrors();
            if (!empty($errors)) {
                $io->warning('Warnings:');
                foreach ($errors as $error) {
                    $io->writeln("  - $error");
                }
            }
            
            return Command::SUCCESS;
        } else {
            $io->error('Asset compilation failed');
            
            $errors = $this->assetManager->getErrors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $io->error($error);
                }
            }
            
            return Command::FAILURE;
        }
    }
}