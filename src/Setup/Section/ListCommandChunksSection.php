<?php

namespace DalaiLomo\ACE\Setup\Section;

use Symfony\Component\Yaml\Yaml;

class ListCommandChunksSection extends AbstractSection implements FileReadable
{
    private $filePath;

    public function getSectionName()
    {
        return 'List command chunks';
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function doAction()
    {
        $config = Yaml::parse(file_get_contents($this->filePath));

        $output = '';
        foreach ($config['ace']['command-chunks'] as $chunkName => $chunk) {
            $output .= sprintf("<fg=green>%s</>\n%s\n", $chunkName, implode(PHP_EOL, $chunk));
        }

        return $output;
    }
}
