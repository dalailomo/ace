<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Setup\Menu\InteractiveMenu;
use DalaiLomo\ACE\Setup\Section\ExecutorSection\ExecutorGroupSection;
use RomaricDrigon\MetaYaml\Exception\NodeValidatorException;

class ExecutorSection extends AbstractSection
{
    public function getSectionName()
    {
        return 'Execute commands';
    }

    public function doAction()
    {
        try {
            $this->config->validateConfig();
        } catch (NodeValidatorException $e) {
            $this->output->writeln('<error>Validation of config file failed: ' . $e->getMessage() . '</error>');

            readline('Please check your config file. Press "Enter" to continue');

            $this->output->writeln(CommandOutputHelper::clearOutput());

            return;
        }

        $interactiveMenu = new InteractiveMenu($this->input, $this->output, $this->question, $this->config);
        $interactiveMenu->setOptionQuitText('Back to main menu');

        $this->config->onEachKey(function($groups, $key) use ($interactiveMenu) {
            $interactiveMenu->registerSection(new ExecutorGroupSection($groups, $key));
        });

        $interactiveMenu->run();

        $this->output->writeln(CommandOutputHelper::clearOutput());
    }
}
