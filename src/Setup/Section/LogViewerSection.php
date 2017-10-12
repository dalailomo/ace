<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
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
        $logFileList = $this->getLogFileList();

        $interactiveMenu = new InteractiveMenu($this->input, $this->output, $this->question, $this->config);
        $interactiveMenu->setOptionQuitText('Back to main menu');

        $i = 0;
        array_map(function($logFile) use($interactiveMenu, &$i) {
            if ($i >= 10) {
                return;
            }
            $interactiveMenu->registerSection(new LogFileSection(self::LOG_FILES_DIR . $logFile));
            $i++;
        }, $logFileList);

        $interactiveMenu->run();

        $this->output->writeln(CommandOutputHelper::clearOutput());
    }

    private function getLogFileList()
    {
        $logFiles = array_filter(scandir(self::LOG_FILES_DIR), function($file) {
            return strpos($file, '.log.json');
        });

        arsort($logFiles);

        return $logFiles;
    }
}
