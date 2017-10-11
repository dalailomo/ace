<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Setup\Menu\InteractiveMenu;
use DalaiLomo\ACE\Setup\Section\EditConfigurationFileSection;
use DalaiLomo\ACE\Setup\Section\ListCommandChunksSection;
use DalaiLomo\ACE\Setup\Section\LogViewerSection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('ace:setup')
            ->setDescription('ACE interactive configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = ACE_ROOT_DIR . 'config.yml';

        InteractiveMenu::create($input, $output, $this->getHelper('question'))
            ->registerSection(ListCommandChunksSection::create()->setFilePath($configFile))
            ->registerSection(EditConfigurationFileSection::create()->setFilePath($configFile))
            ->registerSection(LogViewerSection::create())
            ->run();

        return 0;
    }
}
