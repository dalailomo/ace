<?php

namespace DalaiLomo\ACE\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ACECommand extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return $this->doExecute();
    }

    abstract protected function doExecute();

    protected function clearOutput()
    {
        $this->output->write(sprintf("\033\143"));
    }

    protected function oldSchoolSeparator()
    {
        $this->output->writeln("-------------------------------------------------");
    }

    protected function ninjaSeparator()
    {
        $this->output->writeln("");
    }

    protected function reset()
    {
        $this->clearOutput();
        $this->output->writeln("ACE (Async Command Executor) by DalaiLomo");
        $this->oldSchoolSeparator();
    }
}
