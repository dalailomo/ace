<?php

namespace DalaiLomo\ACE\Group;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Helper\OS;
use DalaiLomo\ACE\Process\ProcessGroup;
use DalaiLomo\ACE\Process\ProcessMonitoring;
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

    private function addProcessGroup($processGroup, $command) {
        $process = new Process($command);

        $this->output->writeln(
            sprintf(
                "Started: <info>%s</info>",
                $command
            )
        );

        $processGroup->add($process);
    }

    private function hasWildcards($string) {
        return strpos($string, '*');
    }

    private function processCommandWithWildcards($processGroup, $command) {
        $commandParts = explode(' ', $command);
        $cmd = '';
        $lines = [];
        static $i = 0;

        foreach($commandParts as $part) {
            if (!$this->hasWildcards($part)) {
                $cmd .= $part . ' ';
                continue;
            }

            exec('ls ' . $part, $lines);
            $cmd .= '__LINESOUT__ ';
        }

        $this->output->writeln(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln("<info>A wildcard has been found and these are the matches: </info>");
        $this->output->writeln(CommandOutputHelper::oldSchoolSeparator());
        $this->output->writeln(implode(PHP_EOL, $lines));
        $this->output->write(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln(CommandOutputHelper::oldSchoolSeparator());
        $this->output->writeln("<fg=yellow>If you think it's going to be an expensive operation split the process into more groups or you computer may burn like a <fg=red>hellish</> creature.</>");
        $this->output->write(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln("Are you sure you want to execute these asynchronously?");

        $result = readline(PHP_EOL . "Y/N: ");

        if (strtolower($result) !== 'y') {
            $this->output->writeln("<fg=red>Cancelled cowardly</>");
            return;
        }

        $this->output->writeln("<info>Executing bravely</info>");

        foreach($lines as $line) {
            $this->addProcessGroup($processGroup, str_replace('__LINESOUT__', $line, str_replace('$I', $i, $cmd)));
            $i++;
        }
    }

    private function createAndRunProcessGroup($groupName)
    {
        $this->output->writeln(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln(sprintf("Starting process group <info>%s</info>", $groupName));

        $processGroup = new ProcessGroup(EventFactory::create(), $this->input, $this->output);
        $processMonitoring = OS::getProcessMonitoring($this->output);

        if ($processMonitoring) {
            $processGroup->addProcessMonitoring($processMonitoring);
        }

        $this->config->onEachProcess($this->key, $groupName, function($command) use($processGroup) {
            if ($this->hasWildcards($command)) {
                $this->processCommandWithWildcards($processGroup, $command);
                return;
            }

            $this->addProcessGroup($processGroup, $command);
        });

        $processGroup->runLoop();

        $this->commandsOutput[$groupName] = $processGroup->getCommandsOutput();
    }
}
