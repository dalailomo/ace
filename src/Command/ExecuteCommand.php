<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Group\GroupExecutor;
use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Helper\FileHandler;
use DalaiLomo\ACE\Log\LogScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends Command
{
    /**
     * @var GroupExecutor
     */
    private $groupExecutor;

    protected function configure()
    {
        $this
            ->setName('execute')
            ->setDescription('Executes commands asynchronously, weeeee!');

        $this->addOption('diagnosis', 'd', InputOption::VALUE_NONE, 'Show process diagnosis output while running.');
        $this->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'Key for groups to execute.');
        $this->addArgument('groups', InputArgument::IS_ARRAY, 'Filter by groups.', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getOption('key');
        $fileResolver = new FileHandler(ACE_CONFIG_FILE);
        $config = new ACEConfig($fileResolver->getAbsolutePath());

        $this->groupExecutor = new GroupExecutor($config, $key, $input, $output);
        $this->groupExecutor->executeProcessGroups();

        $output->writeln(CommandOutputHelper::ninjaSeparator());
        $output->writeln(sprintf("Time spent: <info>%s seconds</info>", $this->groupExecutor->getTimeSpent()));

        $output->writeln('Log file: ' . $this->logToFile($key));
        $output->writeln(CommandOutputHelper::ninjaSeparator());

        return 0;
    }

    private function logToFile($key)
    {
        $file = new \SplFileObject(sprintf('%s/%s.%s%s', ACE_FILES_LOG_DIR, time(), $key, LogScanner::LOG_EXTENSION), 'w');
        $file->fwrite(json_encode([$key => $this->groupExecutor->getCommandsOutput()]));
        return $file->getRealPath();
    }
}
