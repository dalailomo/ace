<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Log\LogScanner;
use DalaiLomo\ACE\Setup\Menu\InteractiveMenu;
use DalaiLomo\ACE\Setup\Section\LogViewerSection\LogFileSection;

class LogViewerSection extends AbstractSection
{
    const LOG_FILES_DIR = ACE_ROOT_DIR . 'var/log/';

    public function getSectionName()
    {
        return 'Logs';
    }

    public function doAction()
    {
        $logScanner = new LogScanner(self::LOG_FILES_DIR);

        $interactiveMenu = new InteractiveMenu($this->input, $this->output, $this->question, $this->config);
        $interactiveMenu->setOptionQuitText('Back to main menu');

        $logScanner->onEachLogFile(function($logFile) use ($interactiveMenu) {
            $interactiveMenu->registerSection(new LogFileSection($logFile));
        });

        $interactiveMenu->run();

        $this->output->writeln(CommandOutputHelper::clearOutput());
    }
}
