<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use OOPress\Extension\ExtensionLoader;
use OOPress\Admin\Health\ModuleHealthChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * ModuleListCommand — Lists installed modules.
 * 
 * @api
 */
class ModuleListCommand extends Command
{
    protected static $defaultName = 'module:list';
    
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
        private readonly ModuleHealthChecker $healthChecker,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('List installed modules')
            ->addOption('health', null, InputOption::VALUE_NONE, 'Show health status')
            ->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Show detailed information');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $modules = $this->extensionLoader->getModules();
        
        if (empty($modules)) {
            $io->warning('No modules installed.');
            return Command::SUCCESS;
        }
        
        $showHealth = $input->getOption('health');
        $verbose = $input->getOption('verbose');
        
        $io->title('Installed Modules');
        
        if ($verbose) {
            // Verbose output with full details
            foreach ($modules as $moduleId => $module) {
                $io->section($module->name . ' (' . $moduleId . ')');
                $io->writeln(sprintf('  Version: %s (%s)', $module->version, $module->stability));
                $io->writeln(sprintf('  Description: %s', $module->description ?? 'No description'));
                $io->writeln(sprintf('  API Target: %s', $module->getApiConstraint()));
                
                if ($showHealth) {
                    $health = $this->healthChecker->getModuleHealth($moduleId);
                    if ($health) {
                        $io->writeln(sprintf('  Health: %s', $health->getStatusLabel()));
                        if ($health->getWarnings()) {
                            $io->writeln('  Warnings:');
                            foreach ($health->getWarnings() as $warning) {
                                $io->writeln(sprintf('    - %s', $warning));
                            }
                        }
                        if ($health->getErrors()) {
                            $io->writeln('  Errors:');
                            foreach ($health->getErrors() as $error) {
                                $io->writeln(sprintf('    - %s', $error));
                            }
                        }
                    }
                }
                
                $io->newLine();
            }
        } else {
            // Compact table output
            $rows = [];
            foreach ($modules as $moduleId => $module) {
                $row = [
                    $moduleId,
                    $module->version,
                    $module->stability,
                    substr($module->description ?? '', 0, 50),
                ];
                
                if ($showHealth) {
                    $health = $this->healthChecker->getModuleHealth($moduleId);
                    $row[] = $health ? $health->getStatusLabel() : 'Unknown';
                }
                
                $rows[] = $row;
            }
            
            $headers = ['ID', 'Version', 'Stability', 'Description'];
            if ($showHealth) {
                $headers[] = 'Health';
            }
            
            $io->table($headers, $rows);
        }
        
        $io->newLine();
        $io->writeln(sprintf('Total modules: %d', count($modules)));
        
        return Command::SUCCESS;
    }
}