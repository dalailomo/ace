<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\Env;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class EditConfigurationFileSection extends AbstractSection
{
    public function getSectionName()
    {
        return 'Edit configuration file';
    }

    public function doAction()
    {
        $process = new Process(Env::getEditor() . ' ' . $this->config->getConfigFilePath());

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        return '';
    }
}
