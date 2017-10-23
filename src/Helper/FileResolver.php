<?php

namespace DalaiLomo\ACE\Helper;

class FileResolver
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getFilePath()
    {
        $file = $this->isInputConfigAnAbsolutePath()
            ? $file = $this->file
            : getcwd() . '/' . $this->file;

        if (null === $this->file || false === $this->fileExists($file)) {
            throw new \InvalidArgumentException('File not found');
        }

        return $file;
    }

    private function isInputConfigAnAbsolutePath()
    {
        return '/' === substr($this->file, 0, 1);
    }

    private function fileExists($filePath)
    {
        return file_exists($filePath);
    }
}
