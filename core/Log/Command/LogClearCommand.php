<?php

declare(strict_types=1);

namespace OOPress\Log\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * LogClearCommand — Clears logs.
 * 
 * @api
 */
class LogClearCommand extends Command
{
    protected static $defaultName = 'log:clear';
    
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Clear logs')
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Keep logs from the last X days')
            ->addOption('level', 'l', InputOption::VALUE_REQUIRED, 'Clear only specific level');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $days = $input->getOption('days');
        $level = $input->getOption('level');
        
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('oop_logs');
        
        if ($days) {
            $qb->andWhere('created_at < DATE_SUB(NOW(), :days)')
               ->setParameter('days', $days);
            $io->note(sprintf('Keeping logs from the last %d days', $days));
        }
        
        if ($level) {
            $qb->andWhere('level = :level')
               ->setParameter('level', $level);
            $io->note(sprintf('Clearing only %s level logs', $level));
        }
        
        $count = $qb->executeStatement();
        
        // Also clear file log
        $logPath = __DIR__ . '/../../../var/logs/oopress.log';
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            $io->writeln('File log cleared');
        }
        
        $io->success(sprintf('Cleared %d log entries', $count));
        
        return Command::SUCCESS;
    }
}