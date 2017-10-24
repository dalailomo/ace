<?php

namespace DalaiLomo\ACE\Helper;

class FileHandler
{
    const ALREADY_EXISTS = 1;
    const CREATED_SUCCESSFULLY = 0;
    const NOT_CREATED = -1;

    private $absolutePath;

    public function __construct($path)
    {
        $this->absolutePath = $this->convertToAbsolutePath($path);
    }

    public function createDirectoryIfNotExists()
    {
        if ($this->directoryExists()) {
            return self::ALREADY_EXISTS;
        }

        $success = mkdir($this->absolutePath, 0700, true);

        if (false === $success) {
            return self::NOT_CREATED;
        }

        return self::CREATED_SUCCESSFULLY;
    }

    public function createFileIfNotExists($fileContent)
    {
        if ($this->fileExists()) {
            return self::ALREADY_EXISTS;
        }

        $success = file_put_contents($this->absolutePath, $fileContent);

        if (false === $success) {
            return self::NOT_CREATED;
        }

        return self::CREATED_SUCCESSFULLY;
    }

    public function directoryExists()
    {
        return is_dir($this->absolutePath);
    }

    public function fileExists()
    {
        return file_exists($this->absolutePath);
    }

    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    private function convertToAbsolutePath($path)
    {
        return self::isAbsolutePath($path)
            ? $path
            : getcwd() . '/' . $path;
    }

    private function isAbsolutePath($path)
    {
        return '/' === substr($path, 0, 1);
    }
}
