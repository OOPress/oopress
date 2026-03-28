<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use OOPress\Update\UpdateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * UpdateApplyCommand — Applies updates.
 * 
 * @api
 */
class UpdateApplyCommand extends Command
{
    protected static $defaultName = 'update:apply';
    
    public function __construct(
        private readonly UpdateManager $updateManager,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Apply available updates')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type of update (core or module)', 'core')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Module ID (if updating a module)')
            ->addOption('version', 'v', InputOption::VALUE_REQUIRED, 'Specific version to update to')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate update without making changes')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $type = $input->getArgument('type');
        $identifier = $input->getArgument('identifier');
        $version = $input->getOption('version');
        $dryRun = $input->getOption('dry-run');
        $skipConfirm = $input->getOption('yes');
        
        if ($dryRun) {
            $io->note('Dry run mode - no changes will be made');
        }
        
        if ($type === 'core') {
            return $this->updateCore($io, $version, $dryRun, $skipConfirm);
        } elseif ($type === 'module') {
            if (!$identifier) {
                $io->error('Module ID is required for module updates.');
                return Command::FAILURE;
            }
            return $this->updateModule($io, $identifier, $version, $dryRun, $skipConfirm);
        } else {
            $io->error(sprintf('Unknown update type: %s. Use "core" or "module".', $type));
            return Command::FAILURE;
        }
    }
    
    private function updateCore(SymfonyStyle $io, ?string $version, bool $dryRun, bool $skipConfirm): int
    {
        $updates = $this->updateManager->checkUpdates('core');
        
        if (empty($updates)) {
            $io->info('No core updates available.');
            return Command::SUCCESS;
        }
        
        $targetUpdate = null;
        
        if ($version) {
            foreach ($updates as $update) {
                if ($update->version === $version) {
                    $targetUpdate = $update;
                    break;
                }
            }
            
            if (!$targetUpdate) {
                $io->error(sprintf('Version %s not found in available updates.', $version));
                return Command::FAILURE;
            }
        } else {
            $targetUpdate = $updates[0]; // Latest update
        }
        
        $io->section(sprintf('Updating core to version %s', $targetUpdate->version));
        
        if ($targetUpdate->isUrgent()) {
            $io->warning('This is a SECURITY update!');
        }
        
        if (!$skipConfirm && !$io->confirm('Proceed with update?', true)) {
            $io->warning('Update cancelled.');
            return Command::SUCCESS;
        }
        
        $result = $this->updateManager->updateCore($targetUpdate->version, $dryRun);
        
        if ($result->success) {
            $io->success($result->message);
            if ($result->migrationsExecuted > 0) {
                $io->writeln(sprintf('Executed %d migrations.', $result->migrationsExecuted));
            }
            return Command::SUCCESS;
        } else {
            $io->error($result->message);
            foreach ($result->errors as $error) {
                $io->error($error);
            }
            return Command::FAILURE;
        }
    }
    
    private function updateModule(SymfonyStyle $io, string $moduleId, ?string $version, bool $dryRun, bool $skipConfirm): int
    {
        $updates = $this->updateManager->checkUpdates('module', $moduleId);
        
        if (empty($updates)) {
            $io->info(sprintf('No updates available for module: %s', $moduleId));
            return Command::SUCCESS;
        }
        
        $targetUpdate = null;
        
        if ($version) {
            foreach ($updates as $update) {
                if ($update->version === $version) {
                    $targetUpdate = $update;
                    break;
                }
            }
            
            if (!$targetUpdate) {
                $io->error(sprintf('Version %s not found for module %s.', $version, $moduleId));
                return Command::FAILURE;
            }
        } else {
            $targetUpdate = $updates[0]; // Latest update
        }
        
        $io->section(sprintf('Updating module %s to version %s', $moduleId, $targetUpdate->version));
        
        if ($targetUpdate->isUrgent()) {
            $io->warning('This is a SECURITY update!');
        }
        
        if (!$skipConfirm && !$io->confirm('Proceed with update?', true)) {
            $io->warning('Update cancelled.');
            return Command::SUCCESS;
        }
        
        $result = $this->updateManager->updateModule($moduleId, $targetUpdate->version, $dryRun);
        
        if ($result->success) {
            $io->success($result->message);
            if ($result->migrationsExecuted > 0) {
                $io->writeln(sprintf('Executed %d migrations.', $result->migrationsExecuted));
            }
            return Command::SUCCESS;
        } else {
            $io->error($result->message);
            foreach ($result->errors as $error) {
                $io->error($error);
            }
            return Command::FAILURE;
        }
    }
}