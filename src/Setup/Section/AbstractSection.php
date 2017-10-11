<?php

namespace DalaiLomo\ACE\Setup\Section;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
     * @var array
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

    public function setAndParseConfig($configFilePath)
    {
        $this->configFilePath = $configFilePath;
        $this->config = Yaml::parse(file_get_contents($configFilePath));
    }
}
