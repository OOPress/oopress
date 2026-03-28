<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use OOPress\Update\UpdateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * UpdateCheckCommand — Checks for available updates.
 * 
 * @api
 */
class UpdateCheckCommand extends Command
{
    protected static $defaultName = 'update:check';
    
    public function __construct(
        private readonly UpdateManager $updateManager,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Check for available updates')
            ->addOption('core', null, InputOption::VALUE_NONE, 'Check core updates only')
            ->addOption('modules', null, InputOption::VALUE_NONE, 'Check module updates only')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output in JSON format');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $checkCore = !$input->getOption('modules') || $input->getOption('core');
        $checkModules = !$input->getOption('core') || $input->getOption('modules');
        
        $updates = [];
        
        if ($checkCore) {
            $io->text('Checking for core updates...');
            $coreUpdates = $this->updateManager->checkUpdates('core');
            $updates['core'] = $coreUpdates;
        }
        
        if ($checkModules) {
            $io->text('Checking for module updates...');
            $moduleUpdates = $this->updateManager->checkUpdates('module');
            $updates['modules'] = $moduleUpdates;
        }
        
        if ($input->getOption('json')) {
            $output->writeln(json_encode($updates, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }
        
        $io->title('Update Check Results');
        
        if ($checkCore) {
            $io->section('Core Updates');
            if (empty($updates['core'])) {
                $io->info('No core updates available.');
            } else {
                foreach ($updates['core'] as $update) {
                    $urgency = $update->isUrgent() ? ' [SECURITY]' : '';
                    $io->writeln(sprintf(
                        '  ✓ OOPress %s available (%s)%s',
                        $update->version,
                        $update->stability,
                        $urgency
                    ));
                    $io->writeln(sprintf('    Release date: %s', $update->releaseDate));
                    $io->writeln(sprintf('    Notes: %s', $update->releaseNotes));
                }
            }
        }
        
        if ($checkModules) {
            $io->section('Module Updates');
            if (empty($updates['modules'])) {
                $io->info('No module updates available.');
            } else {
                foreach ($updates['modules'] as $update) {
                    $urgency = $update->isUrgent() ? ' [SECURITY]' : '';
                    $io->writeln(sprintf(
                        '  ✓ %s %s available (%s)%s',
                        $update->moduleId,
                        $update->version,
                        $update->stability,
                        $urgency
                    ));
                    $io->writeln(sprintf('    Release date: %s', $update->releaseDate));
                }
            }
        }
        
        $totalUpdates = count($updates['core'] ?? []) + count($updates['modules'] ?? []);
        
        if ($totalUpdates > 0) {
            $io->success(sprintf('Found %d update(s) available.', $totalUpdates));
        } else {
            $io->success('All components are up to date.');
        }
        
        return Command::SUCCESS;
    }
}