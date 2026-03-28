<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use OOPress\Migration\MigrationRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * MigrateCommand — Runs database migrations.
 * 
 * @api
 */
class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';
    
    public function __construct(
        private readonly MigrationRunner $migrationRunner,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Run database migrations')
            ->setHelp('This command runs pending database migrations.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate migration without making changes')
            ->addOption('version', 'v', InputOption::VALUE_REQUIRED, 'Migrate to a specific version')
            ->addOption('status', 's', InputOption::VALUE_NONE, 'Show migration status');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Show status
        if ($input->getOption('status')) {
            return $this->showStatus($io);
        }
        
        // Check if up to date
        if ($this->migrationRunner->isUpToDate()) {
            $io->success('Database is up to date.');
            return Command::SUCCESS;
        }
        
        $dryRun = $input->getOption('dry-run');
        $version = $input->getOption('version');
        
        if ($dryRun) {
            $io->note('Dry run mode - no changes will be made');
        }
        
        // Run migrations
        $io->section('Running migrations...');
        
        if ($version) {
            $result = $this->migrationRunner->migrateTo($version, $dryRun);
        } else {
            $result = $this->migrationRunner->migrate($dryRun);
        }
        
        if ($result->success) {
            if ($result->migrationsExecuted > 0) {
                $io->success(sprintf(
                    'Successfully executed %d migration(s)',
                    $result->migrationsExecuted
                ));
                $io->writeln($result->output);
            } else {
                $io->info('No migrations to execute.');
            }
            
            return Command::SUCCESS;
        } else {
            $io->error('Migration failed');
            $io->writeln($result->output);
            $io->error($result->getErrorMessage());
            
            return Command::FAILURE;
        }
    }
    
    private function showStatus(SymfonyStyle $io): int
    {
        $io->title('Migration Status');
        
        $available = $this->migrationRunner->getAvailableMigrations();
        $executed = $this->migrationRunner->getExecutedMigrations();
        
        $io->section('Available Migrations');
        
        if (empty($available)) {
            $io->info('No migrations available.');
        } else {
            $rows = [];
            foreach ($available as $version => $class) {
                $status = in_array($version, $executed) ? '✓ Executed' : '○ Pending';
                $rows[] = [$version, $class, $status];
            }
            $io->table(['Version', 'Class', 'Status'], $rows);
        }
        
        $io->section('Summary');
        $io->writeln(sprintf(
            'Total: %d | Executed: %d | Pending: %d',
            count($available),
            count($executed),
            count($available) - count($executed)
        ));
        
        $isUpToDate = $this->migrationRunner->isUpToDate();
        
        if ($isUpToDate) {
            $io->success('Database is up to date.');
        } else {
            $io->warning('Database has pending migrations.');
        }
        
        return Command::SUCCESS;
    }
}