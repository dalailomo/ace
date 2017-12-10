<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Config\InvalidKeyException;
use DalaiLomo\ACE\Group\GroupExecutor;
use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Helper\FileHandler;
use DalaiLomo\ACE\Log\LogScanner;
use DalaiLomo\ACE\Log\LogWriter;
use RomaricDrigon\MetaYaml\Loader\YamlLoader;
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
        $config = new ACEConfig($fileResolver->getAbsolutePath(), new YamlLoader());
        $config->validateConfig();

        if (!$key) {
            $output->writeln(PHP_EOL . 'You must specify a <fg=green>--key (-k)</>.' . PHP_EOL);
            $output->writeln($config->getSummary());
            return -1;
        }

        try {
            $this->groupExecutor = new GroupExecutor($config, $key, $input, $output);
            $this->groupExecutor->executeProcessGroups();
        } catch (InvalidKeyException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            $output->writeln("\nAvailable keys:\n\n" . $config->getSummary());
            return -1;
        }

        $output->writeln(CommandOutputHelper::ninjaSeparator());
        $output->writeln(sprintf("Time spent: <info>%s seconds</info>", $this->groupExecutor->getTimeSpent()));

        $logWriter = new LogWriter(ACE_FILES_LOG_DIR);
        $logFilePath = $logWriter->logToFile($this->groupExecutor->getCommandsOutput(), $key);

        $output->writeln('Log file: ' . $logFilePath);
        $output->writeln(CommandOutputHelper::ninjaSeparator());

        return 0;
    }
}
