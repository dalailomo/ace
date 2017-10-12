<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;

class ListCommandChunksSection extends AbstractSection
{
    private $sectionOutput = '';

    public function getSectionName()
    {
        return 'List command chunks';
    }

    public function doAction()
    {
        $this->config->onEachKey(function($chunkList, $key) {
            $this->sectionOutput .= sprintf(
                "<fg=magenta>%s</>", $key . PHP_EOL . CommandOutputHelper::oldSchoolSeparator()
            );

            $this->config->onEachChunk($key, function($commandChunk, $chunkName) {
                $this->sectionOutput .= sprintf(
                    "<fg=green>%s</>\n%s\n\n", $chunkName, implode(PHP_EOL, $commandChunk)
                );
            });
        });

        return $this->sectionOutput;
    }
}
