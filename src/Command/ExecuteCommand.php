<?php

namespace DalaiLomo\ACE\Command;

use React\ChildProcess\Process;
use React\EventLoop\Factory as EventFactory;
use Symfony\Component\Yaml\Yaml;

class ExecuteCommand extends ACECommand
{
    protected function configure()
    {
        $this
            ->setName('ace:execute')
            ->setDescription('Executes commands asynchronously, weeeee!');
    }

    protected function doExecute()
    {
        $start_time = microtime(true);

        $config = Yaml::parse(file_get_contents('config.yml'));

        if (null === $config) {
            $this->output->writeln("<error>Config file does not exists</error>");
            return -1;
        }

        foreach($config['ace']['command-chunks'] as $chunkName => $commandChunk) {
            $loop = EventFactory::create();

            $this->output->writeln("");
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

                $process->stderr->on('data', function($outputChunk) use($command, $process) {
                    $this->output->writeln("");
                    $this->output->writeln(
                        sprintf(
                            "<error>ERROR HAPPENED ON PROCESS</error> <fg=magenta>%s</> : <info>'%s</info>'",
                            $process->getPid(),
                            $command
                        )
                    );
                    $this->output->writeln("<fg=red>$outputChunk</>");
                });

                $process->on('exit', function() use($command, $process) {
                    $this->output->writeln(
                        sprintf(
                            "Finished <fg=magenta>%s</> : <info>%s</info>",
                            $process->getPid(),
                            $command
                        )
                    );
                });
            }

            $loop->run();
        }

        $end_time = microtime(true);

        $this->output->writeln("");
        $this->output->writeln(sprintf("Time spent: <info>%s seconds<info></info>", round($end_time - $start_time, 2)));

        return 0;
    }
}
