<?php

namespace Console\Command;

use Model\Todo;
use Katzgrau\KLogger\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class TodoCreateCommand extends SymfonyCommand
{
    private $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('todo:create')
            ->setDescription('Create a new todo with status and description')
            ->setHelp('This command allows you to create a new todo item.')
            ->addArgument('status', InputArgument::REQUIRED, 'The status of the todo (NEW|INPROGRESS|ONHOLD|DONE)')
            ->addArgument('description', InputArgument::REQUIRED, 'The description of the todo.');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @SuppressWarnings("unused")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $todo = new Todo($this->logger);
        $todo->setStatus($input->getArgument('status'));
        $todo->description = $input->getArgument('description');
        $todo->save();
    }
}
