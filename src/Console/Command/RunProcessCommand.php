<?php

namespace Nodez\Inline\Console\Command;

use DataObject\Configuration;
use Nodez\Core\Process\Engine\ProcessEngine;
use Nodez\Core\Process\ProcessFactory;
use Nodez\Core\Process\ProcessJsonHydrator;
use Nodez\Core\Process\Reader\DirectoryProcessReader;
use Nodez\Core\Process\Validator\ProcessValidator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Reepository\ConfigurationRepository;
use Symfony\Component\Console\Attribute as Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Console\AsCommand(
    name: 'nodez:run:process',
    description: 'Run a process and pass in context data.'
)]
class RunProcessCommand extends Command
{

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ProcessEngine $engine,
        protected ProcessFactory $factory,
        protected ProcessValidator $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('process', InputArgument::REQUIRED, 'The process key to process.');
        $this->addArgument('context', InputArgument::OPTIONAL, 'Data used to init the context.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Test Point');
        $process = $this->factory->build();
        $errors = $this->validator->validate($process);
        if (!empty($errors)) {
            $output->writeln('ERRORS');
            foreach ($errors as $error) {
                $output->writeln(sprintf('<error>%s</error>', $error));
            }
            return Command::FAILURE;
        }

        $this->engine->process($process);
        //$context = $process->getContext();
        $output->writeln('Done!');
        //$output->writeln('Context: ' . print_r($context, true));
        return Command::SUCCESS;
    }
}