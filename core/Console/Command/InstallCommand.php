<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use OOPress\Installer\Installer;
use OOPress\Installer\InstallerConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * InstallCommand — Installs OOPress via CLI.
 * 
 * @api
 */
class InstallCommand extends Command
{
    protected static $defaultName = 'install';
    
    public function __construct(
        private readonly Installer $installer,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Install OOPress')
            ->setHelp('This command installs OOPress with the provided configuration.')
            ->addOption('site-name', null, InputOption::VALUE_REQUIRED, 'Site name')
            ->addOption('site-url', null, InputOption::VALUE_REQUIRED, 'Site URL')
            ->addOption('admin-username', null, InputOption::VALUE_REQUIRED, 'Admin username')
            ->addOption('admin-email', null, InputOption::VALUE_REQUIRED, 'Admin email')
            ->addOption('admin-password', null, InputOption::VALUE_REQUIRED, 'Admin password')
            ->addOption('db-host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('db-port', null, InputOption::VALUE_REQUIRED, 'Database port')
            ->addOption('db-name', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('db-user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('db-password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force installation even if already installed');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Check if already installed
        if ($this->installer->isInstalled() && !$input->getOption('force')) {
            $io->error('OOPress is already installed. Use --force to reinstall.');
            return Command::FAILURE;
        }
        
        $io->title('OOPress Installation');
        
        // Gather configuration
        $config = $this->gatherConfiguration($input, $output, $io);
        
        // Validate configuration
        $errors = $config->validate();
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $io->error($error);
            }
            return Command::FAILURE;
        }
        
        // Confirm installation
        if (!$io->confirm('Proceed with installation?', true)) {
            $io->warning('Installation cancelled.');
            return Command::SUCCESS;
        }
        
        // Run installation
        $io->section('Installing OOPress...');
        
        $result = $this->installer->install($config);
        
        if ($result->success) {
            $io->success($result->message);
            $io->section('Installation Summary');
            $io->writeln($result->getSummary());
            
            return Command::SUCCESS;
        } else {
            $io->error($result->message);
            $io->section('Installation Errors');
            foreach ($result->errors as $error) {
                $io->error($error);
            }
            
            return Command::FAILURE;
        }
    }
    
    private function gatherConfiguration(InputInterface $input, OutputInterface $output, SymfonyStyle $io): InstallerConfig
    {
        $helper = $this->getHelper('question');
        
        // Site information
        $siteName = $input->getOption('site-name');
        if (!$siteName) {
            $question = new Question('Site name: ', 'My OOPress Site');
            $siteName = $helper->ask($input, $output, $question);
        }
        
        $siteUrl = $input->getOption('site-url');
        if (!$siteUrl) {
            $question = new Question('Site URL: ', 'https://example.com');
            $siteUrl = $helper->ask($input, $output, $question);
        }
        
        // Admin user
        $adminUsername = $input->getOption('admin-username');
        if (!$adminUsername) {
            $question = new Question('Admin username: ', 'admin');
            $adminUsername = $helper->ask($input, $output, $question);
        }
        
        $adminEmail = $input->getOption('admin-email');
        if (!$adminEmail) {
            $question = new Question('Admin email: ');
            $adminEmail = $helper->ask($input, $output, $question);
        }
        
        $adminPassword = $input->getOption('admin-password');
        if (!$adminPassword) {
            $question = new Question('Admin password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $adminPassword = $helper->ask($input, $output, $question);
            
            // Confirm password
            $question = new Question('Confirm password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $confirmPassword = $helper->ask($input, $output, $question);
            
            if ($adminPassword !== $confirmPassword) {
                $io->error('Passwords do not match.');
                return $this->gatherConfiguration($input, $output, $io);
            }
        }
        
        // Database configuration
        $dbHost = $input->getOption('db-host');
        if (!$dbHost) {
            $question = new Question('Database host: ', 'localhost');
            $dbHost = $helper->ask($input, $output, $question);
        }
        
        $dbPort = $input->getOption('db-port');
        if (!$dbPort) {
            $question = new Question('Database port: ', '3306');
            $dbPort = $helper->ask($input, $output, $question);
        }
        
        $dbName = $input->getOption('db-name');
        if (!$dbName) {
            $question = new Question('Database name: ');
            $dbName = $helper->ask($input, $output, $question);
        }
        
        $dbUser = $input->getOption('db-user');
        if (!$dbUser) {
            $question = new Question('Database user: ');
            $dbUser = $helper->ask($input, $output, $question);
        }
        
        $dbPassword = $input->getOption('db-password');
        if (!$dbPassword) {
            $question = new Question('Database password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $dbPassword = $helper->ask($input, $output, $question);
        }
        
        return new InstallerConfig(
            adminUsername: $adminUsername,
            adminEmail: $adminEmail,
            adminPassword: $adminPassword,
            siteName: $siteName,
            siteUrl: $siteUrl,
            dbHost: $dbHost,
            dbPort: (int) $dbPort,
            dbName: $dbName,
            dbUser: $dbUser,
            dbPassword: $dbPassword,
        );
    }
}