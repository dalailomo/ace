<?php

namespace DalaiLomo\ACE\Command;

use DalaiLomo\ACE\Config\ACEConfig;
use DalaiLomo\ACE\Helper\FileHandler;
use DalaiLomo\ACE\Setup\Menu\InteractiveMenu;
use DalaiLomo\ACE\Setup\Section\EditConfigurationFileSection;
use DalaiLomo\ACE\Setup\Section\ExecutorSection;
use DalaiLomo\ACE\Setup\Section\ListCommandGroupsSection;
use DalaiLomo\ACE\Setup\Section\LogViewerSection;
use RomaricDrigon\MetaYaml\Loader\YamlLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('setup')
            ->setDescription('ACE interactive configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileResolver = new FileHandler(ACE_CONFIG_FILE);
        $config = new ACEConfig($fileResolver->getAbsolutePath(), new YamlLoader());

        $interactiveMenu = new InteractiveMenu($input, $output, $this->getHelper('question'), $config);

        $interactiveMenu->registerSection(new ListCommandGroupsSection());
        $interactiveMenu->registerSection(new ExecutorSection());
        $interactiveMenu->registerSection(new LogViewerSection());
        $interactiveMenu->registerSection(new EditConfigurationFileSection());

        $interactiveMenu->run();

        return 0;
    }
}
