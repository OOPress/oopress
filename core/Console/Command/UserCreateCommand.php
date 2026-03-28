<?php

declare(strict_types=1);

namespace OOPress\Console\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * UserCreateCommand — Creates a new user.
 * 
 * @api
 */
class UserCreateCommand extends Command
{
    protected static $defaultName = 'user:create';
    
    public function __construct(
        private readonly Connection $connection,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setDescription('Create a new user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addOption('password', 'p', InputArgument::OPTIONAL, 'Password (will prompt if not provided)')
            ->addOption('role', 'r', InputArgument::OPTIONAL, 'User role (default: authenticated)', 'authenticated');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getOption('password');
        $role = $input->getOption('role');
        
        // Check if user exists
        $existing = $this->connection->fetchOne(
            'SELECT id FROM oop_users WHERE username = :username OR email = :email',
            ['username' => $username, 'email' => $email]
        );
        
        if ($existing) {
            $io->error('User already exists with that username or email.');
            return Command::FAILURE;
        }
        
        // Get password if not provided
        if (!$password) {
            $helper = $this->getHelper('question');
            
            $question = new Question('Password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
            
            $question = new Question('Confirm password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $confirm = $helper->ask($input, $output, $question);
            
            if ($password !== $confirm) {
                $io->error('Passwords do not match.');
                return Command::FAILURE;
            }
        }
        
        // Validate password strength
        if (strlen($password) < 8) {
            $io->error('Password must be at least 8 characters.');
            return Command::FAILURE;
        }
        
        // Create user
        $hashedPassword = $this->passwordHasher->hashPassword(
            new AdminUser(),
            $password
        );
        
        $roles = ['ROLE_' . strtoupper($role), 'ROLE_USER'];
        
        $this->connection->insert('oop_users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'roles' => json_encode($roles),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        $userId = $this->connection->lastInsertId();
        
        $io->success(sprintf(
            'User created successfully! (ID: %d)',
            $userId
        ));
        
        return Command::SUCCESS;
    }
}