<?php

namespace DalaiLomo\ACE\Setup\Section;

class ListCommandChunksSection extends AbstractSection
{
    public function getSectionName()
    {
        return 'List command chunks';
    }

    public function doAction()
    {
        $output = '';

        foreach ($this->config['ace']['command-chunks'] as $chunkName => $chunk) {
            $output .= sprintf("<fg=green>%s</>\n%s\n", $chunkName, implode(PHP_EOL, $chunk));
        }

        return $output;
    }
}
