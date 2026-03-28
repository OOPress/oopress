<?php

declare(strict_types=1);

namespace OOPress\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OOPress\Kernel;

/**
 * ConsoleApplication — OOPress CLI application.
 * 
 * @api
 */
class ConsoleApplication extends SymfonyApplication
{
    private Kernel $kernel;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        
        parent::__construct('OOPress', $kernel->getVersion());
        
        $this->registerCommands();
    }
    
    private function registerCommands(): void
    {
        // Boot kernel to access services
        $this->kernel->boot();
        
        $container = $this->kernel->getContainer();
        
        // Register all commands from container
        if ($container->has('console.command.install')) {
            $this->add($container->get('console.command.install'));
        }
        if ($container->has('console.command.migrate')) {
            $this->add($container->get('console.command.migrate'));
        }
        if ($container->has('console.command.cache_clear')) {
            $this->add($container->get('console.command.cache_clear'));
        }
        if ($container->has('console.command.module_list')) {
            $this->add($container->get('console.command.module_list'));
        }
        if ($container->has('console.command.user_create')) {
            $this->add($container->get('console.command.user_create'));
        }
        if ($container->has('console.command.update_check')) {
            $this->add($container->get('console.command.update_check'));
        }
        if ($container->has('console.command.update_apply')) {
            $this->add($container->get('console.command.update_apply'));
        }
        
        // Dispatch event for modules to register commands
        $event = new Event\ConsoleCommandsEvent($this);
        $this->kernel->getHookDispatcher()->dispatch($event, 'console.commands.register');
    }
    
    protected function doRun(InputInterface $input, OutputInterface $output): int
    {
        try {
            return parent::doRun($input, $output);
        } finally {
            $this->kernel->shutdown();
        }
    }
}