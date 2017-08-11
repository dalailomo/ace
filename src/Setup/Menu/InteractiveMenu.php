<?php

namespace DalaiLomo\ACE\Setup\Menu;

use DalaiLomo\ACE\Helper\CommandOutputHelper;
use DalaiLomo\ACE\Setup\Section\Section;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class InteractiveMenu implements Menu
{
    const OPTION_QUIT = 0;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @var Section[]
     */
    private $sections = [];

    /**
     * @var array
     */
    private $choices = [
        self::OPTION_QUIT => 'Quit',
    ];

    private function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
    }

    public static function create(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        return new static($input, $output, $questionHelper);
    }

    public function registerSection(Section $section)
    {
        $this->sections[] = $section;

        return $this;
    }

    public function run()
    {
        $this->prepareSections();
        $this->output->write(CommandOutputHelper::clearOutput());

        do {
            $this->output->writeln("----[ Main menu ]----");
            $choice = $this->ask('Select an option');

            if ($choice === $this->choices[self::OPTION_QUIT]) {
                break;
            }

            $this->output->write(CommandOutputHelper::clearOutput());
            $this->output->writeln($this->getSectionFromChoice($choice)->doAction());
        } while (true);

        $this->output->writeln('Bye!');
        return 0;
    }

    /**
     * @param string $choice
     * @return Section
     */
    private function getSectionFromChoice($choice)
    {
        $key = array_search($choice, $this->choices);

        return $this->choices[$key];
    }

    private function ask($text)
    {
        return $this->questionHelper->ask(
            $this->input,
            $this->output,
            new ChoiceQuestion(sprintf('<info>%s</info>', $text), $this->choices)
        );
    }

    private function prepareSections()
    {
        array_map(function(Section $section) {
            $this->choices[] = $section;
        }, $this->sections);
    }
}
