<?php

namespace DalaiLomo\ACE\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class SetupCommand extends ACECommand
{
    const OPTION_LIST_CHUNKS = 'l';
    const OPTION_EDIT_CONFIG = 'e';
    const OPTION_BACK = 'b';
    const OPTION_QUIT = 'q';

    private $choices = [
        'main_menu' => [
            self::OPTION_LIST_CHUNKS => 'List all command chunks',
            self::OPTION_EDIT_CONFIG => 'Edit configuration',
            self::OPTION_QUIT => 'Quit',
        ],
        'edit_config' => [
            self::OPTION_BACK => 'Back',
        ]
    ];

    private $config = [];

    protected function configure()
    {
        $this
            ->setName('ace:setup')
            ->setDescription('ACE interactive configuration');
    }

    protected function doExecute()
    {
        $this->config = Yaml::parse(file_get_contents('config.yml'));
        $this->reset();

        $menuChoice = $this->mainMenu();

        $this->reset();

        switch ($menuChoice) {
            case self::OPTION_LIST_CHUNKS:
                $this->sectionListCommandChunks();
                $this->doExecute();
                break;
            case self::OPTION_EDIT_CONFIG:
                $this->sectionEditConfig();
                $this->doExecute();
                break;
            case self::OPTION_QUIT:
                $this->clearOutput();
                $this->output->writeln('Bye!');
                return 0;
        }

        return 0;
    }

    private function mainMenu()
    {
        $this->output->writeln("Main menu");
        $this->ninjaSeparator();

        $question = $this->getHelper('question');

        return $question->ask($this->input, $this->output, new ChoiceQuestion('<info>Select an option: </info>', $this->choices['main_menu']));
    }

    private function sectionListCommandChunks()
    {
        $this->output->writeln("Listing command chunks");

        foreach ($this->config['ace']['command-chunks'] as $chunkName => $chunk) {
            $this->ninjaSeparator();
            $this->output->writeln(sprintf('<fg=green>%s</>', $chunkName));
            $this->oldSchoolSeparator();
            $this->output->writeln(implode(PHP_EOL, $chunk));
            $this->ninjaSeparator();
        }

        $question = $this->getHelper('question');

        $question->ask($this->input, $this->output, new ChoiceQuestion('', $this->choices['edit_config']));
    }

    private function sectionEditConfig()
    {
        $process = new Process('vim config.yml');

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }
    }
}
