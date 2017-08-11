<?php

namespace DalaiLomo\ACE\Setup\Section;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class EditConfigurationFileSection extends AbstractSection implements FileReadable
{
    private $filePath;

    public function getSectionName()
    {
        return 'Edit configuration file';
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function doAction()
    {
        $process = new Process('vim ' . $this->filePath);

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
