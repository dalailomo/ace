<?php

namespace DalaiLomo\ACE\Group;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Process\ProcessGroup;
use React\ChildProcess\Process;
use React\EventLoop\Factory as EventFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupExecutor
{
    /**
     * @var ACEConfig
     */
    private $config;

    /**
     * @var string
     */
    private $key;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $commandsOutput = [];

    /**
     * @var float
     */
    private $timeSpent;

    public function __construct(ACEConfig $config, $key, InputInterface $input, OutputInterface $output)
    {
        $this->config = $config;
        $this->key = $key;
        $this->input = $input;
        $this->output = $output;
    }

    public function executeProcessGroups()
    {
        $startTime = microtime(true);

        $this->config->onEachGroup($this->key, function($commandGroup, $groupName) {
            $this->createAndRunProcessGroup($groupName);
        }, $this->input->getArgument('groups'));

        $endTime = microtime(true);

        $this->timeSpent = round($endTime - $startTime, 2);
    }

    public function getTimeSpent()
    {
        return $this->timeSpent;
    }

    public function getCommandsOutput()
    {
        return $this->commandsOutput;
    }

    private function createAndRunProcessGroup($groupName)
    {
        $this->output->writeln(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln(sprintf("Starting process group <info>%s</info>", $groupName));

        $processGroup = new ProcessGroup(EventFactory::create(), $this->input, $this->output);

        $this->config->onEachProcess($this->key, $groupName, function($command) use($processGroup) {
            $process = new Process($command);

            $this->output->writeln(
                sprintf(
                    "Started: <info>%s</info>",
                    $command
                )
            );

            $processGroup->add($process);
        });

        $processGroup->runLoop();

        $this->commandsOutput[$groupName] = $processGroup->getCommandsOutput();
    }
}
