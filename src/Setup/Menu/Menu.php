<?php

namespace DalaiLomo\ACE\Setup\Menu;

use DalaiLomo\ACE\Setup\Section\Section;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Menu
{
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        $configFilePath
    );

    public function registerSection(Section $section);

    public function setOptionQuitText($text);

    public function run();
}
