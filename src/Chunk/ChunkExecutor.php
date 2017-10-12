<?php

namespace DalaiLomo\ACE\Chunk;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use React\ChildProcess\Process;
use React\EventLoop\Factory as EventFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChunkExecutor
{
    /**
     * @var array
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

    public function __construct(array $config, $key, InputInterface $input, OutputInterface $output)
    {
        $this->config = $config;
        $this->key = $key;
        $this->input = $input;
        $this->output = $output;
    }

    public function executeChunks()
    {
        foreach($this->config[$this->key]['command-chunks'] as $chunkName => $commandChunk) {
            $loop = EventFactory::create();

            $this->output->writeln(CommandOutputHelper::ninjaSeparator());
            $this->output->writeln(sprintf("Starting chunk <info>%s</info>", $chunkName));

            foreach($commandChunk as $command) {
                $process = new Process($command);
                $process->start($loop);

                $this->output->writeln(
                    sprintf(
                        "Started <fg=magenta>%s</> : <info>%s</info>",
                        $process->getPid(),
                        $command
                    )
                );

                $process->on('exit', function() use($command, $process) {
                    $this->output->writeln(
                        sprintf(
                            "Finished <fg=magenta>%s</> : <info>%s</info> : Exit code (<fg=%s>%s</>)",
                            $process->getPid(),
                            $command,
                            ($process->getExitCode() === -1) ? 'red' : 'green',
                            $process->getExitCode()
                        )
                    );
                });

                $process->stdout->on('data', function($outputChunk) use($command, $process) {
                    $this->collectOutput('stdout', $process->getPid(), $outputChunk, $command);
                });

                $process->stderr->on('data', function($outputChunk) use($command, $process) {
                    $this->collectOutput('stderr', $process->getPid(), $outputChunk, $command);

                    if (false === $this->input->getOption('diagnosis')) {
                        return;
                    }

                    $this->output->writeln(CommandOutputHelper::ninjaSeparator());
                    $this->output->writeln(
                        sprintf(
                            "<fg=blue>Diagnosis output</> <fg=magenta>%s</> : <info>'%s</info>'",
                            $process->getPid(),
                            $command
                        )
                    );
                    $this->output->writeln("$outputChunk");
                });
            }

            $loop->run();
        }
    }

    public function getTimeSpent()
    {
        return 0;
    }


    private function collectOutput($key, $pid, $outputChunk, $command)
    {
        isset($this->output[$pid][$command][$key])
            ? $this->output[$pid][$command][$key] .= $outputChunk
            : $this->output[$pid][$command][$key] = $outputChunk;
    }
}
