<?php

namespace DalaiLomo\ACE\Chunk;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Process\ProcessChunk;
use React\ChildProcess\Process;
use React\EventLoop\Factory as EventFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChunkExecutor
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

    public function executeChunks()
    {
        $startTime = microtime(true);

        $this->config->onEachChunk($this->key, function($commandChunk, $chunkName) {
            $this->createAndRunProcessChunk($chunkName);
        });

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

    private function createAndRunProcessChunk($chunkName)
    {
        $this->output->writeln(CommandOutputHelper::ninjaSeparator());
        $this->output->writeln(sprintf("Starting chunk <info>%s</info>", $chunkName));

        $processChunk = new ProcessChunk(EventFactory::create(), $this->input, $this->output);

        $this->config->onEachProcess($this->key, $chunkName, function($command) use($processChunk) {
            $process = new Process($command);

            $this->output->writeln(
                sprintf(
                    "Started <fg=magenta>%s</> : <info>%s</info>",
                    $process->getPid(),
                    $command
                )
            );

            $processChunk->add($process);
        });

        $processChunk->runLoop();

        $this->commandsOutput = $processChunk->getCommandsOutput();
    }
}
