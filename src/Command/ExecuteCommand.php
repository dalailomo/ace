<?php

namespace DalaiLomo\ACE\Command;

use React\ChildProcess\Process;
use React\EventLoop\Factory as EventFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('ace:execute')
            ->setDescription('Executes commands asynchronously, weeeee!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start_time = microtime(true);

        $config = Yaml::parse(file_get_contents('config.yml'));

        if (null === $config) {
            $output->writeln("<error>Config file does not exists</error>");
            return -1;
        }

        foreach($config['ace']['command-chunks'] as $chunkName => $commandChunk) {
            $loop = EventFactory::create();

            $output->writeln("");
            $output->writeln(sprintf("Starting chunk <info>%s</info>", $chunkName));

            foreach($commandChunk as $command) {
                $process = new Process($command);
                $process->start($loop);

                $output->writeln(
                    sprintf(
                        "Started <fg=magenta>%s</> : <info>%s</info>",
                        $process->getPid(),
                        $command
                    )
                );

                $process->stderr->on('data', function($outputChunk) use($command, $output, $process) {
                    $output->writeln("");
                    $output->writeln(
                        sprintf(
                            "<error>ERROR HAPPENED ON PROCESS</error> <fg=magenta>%s</> : <info>'%s</info>'",
                            $process->getPid(),
                            $command
                        )
                    );
                    $output->writeln("<fg=red>$outputChunk</>");
                });

                $process->on('exit', function() use($command, $output, $process) {
                    $output->writeln(
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

        $output->writeln("");
        $output->writeln(sprintf("Time spent: <info>%s seconds<info></info>", round($end_time - $start_time, 2)));

        return 0;
    }
}
