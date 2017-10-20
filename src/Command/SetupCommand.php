<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Setup\Menu\InteractiveMenu;
use DalaiLomo\ACE\Setup\Section\EditConfigurationFileSection;
use DalaiLomo\ACE\Setup\Section\ListCommandGroupsSection;
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
        $interactiveMenu = new InteractiveMenu(
            $input,
            $output,
            $this->getHelper('question'),
            new ACEConfig(ACE_ROOT_DIR . 'config.yml')
        );

        $interactiveMenu->registerSection(new ListCommandGroupsSection());
        $interactiveMenu->registerSection(new EditConfigurationFileSection());
        $interactiveMenu->registerSection(new LogViewerSection());

        $interactiveMenu->run();

        return 0;
    }
}
