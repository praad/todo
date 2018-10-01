<?php

namespace Console\Command;

use Model\Todo;
use Katzgrau\KLogger\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class TodoListCommand extends SymfonyCommand
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
        $this->setName('todo:list')
            ->setDescription('List all todos')
            ->setHelp('This command list all todos are available.')
            ->addArgument('id', InputArgument::OPTIONAL, 'The id of the todo');
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
        if ($id = $input->getArgument('id')) {
            Todo::find($id, $this->logger);

            return true;
        }
        Todo::all($this->logger);

        return true;
    }
}
