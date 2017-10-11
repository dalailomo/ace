<?php

namespace DalaiLomo\ACE\Setup\Section;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSection implements Section
{
    protected $input;

    protected $output;

    protected $question;

    abstract public function getSectionName();

    abstract public function doAction();

    public static function create()
    {
        return new static();
    }

    public function setInputInterface(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    public function __toString()
    {
        return $this->getSectionName();
    }

    public function setQuestionHelper(QuestionHelper $question)
    {
        $this->question = $question;

        return $this;
    }
}
