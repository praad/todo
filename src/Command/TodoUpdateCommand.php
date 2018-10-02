<?php

namespace Console\Command;

use Model\Todo;
use Katzgrau\KLogger\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class TodoUpdateCommand extends SymfonyCommand
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
        $this->setName('todo:update')
            ->setDescription('Update status of the selected todo.')
            ->setHelp('Update status of the selected todo by id.')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the given todo')
            ->addArgument('status', InputArgument::REQUIRED, 'The new status of the given todo')
            ->addArgument('description', InputArgument::OPTIONAL, 'The new description of the given todo');
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
        $id = $input->getArgument('id');
        if ($todo = Todo::find($id, $this->logger)) {
            $todo->setLogger($this->logger);
            $todo->updateStatus(strtoupper($input->getArgument('status')), $input->getArgument('description'));

            return true;
        }

        return 'Todo can not be found. Give a correct todo id.';
    }
}
