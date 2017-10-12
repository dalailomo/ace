<?php

namespace DalaiLomo\ACE\Setup\Section;

use DalaiLomo\ACE\Config\ACEConfig;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSection implements Section
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var QuestionHelper
     */
    protected $question;

    /**
     * @var ACEConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $configFilePath;

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

    public function setConfig(ACEConfig $ACEConfig)
    {
        $this->config = $ACEConfig;
    }
}
