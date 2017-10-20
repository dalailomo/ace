<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Helper\CommandOutputHelper;

class ListCommandGroupsSection extends AbstractSection
{
    private $sectionOutput = '';

    public function getSectionName()
    {
        return 'List command groups';
    }

    public function doAction()
    {
        $this->config->onEachKey(function($group, $key) {
            $this->sectionOutput .= sprintf(
                "<fg=magenta>%s</>", $key . PHP_EOL . CommandOutputHelper::oldSchoolSeparator()
            );

            $this->config->onEachGroup($key, function($commands, $groupName) {
                $this->sectionOutput .= sprintf(
                    "<fg=green>%s</>\n%s\n\n", $groupName, implode(PHP_EOL, $commands)
                );
            });
        });

        return $this->sectionOutput;
    }
}
