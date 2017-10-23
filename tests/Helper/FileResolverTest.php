<?php

namespace DalaiLomo\ACE\Tests\Helper;

use DalaiLomo\ACE\Helper\FileResolver;
use PHPUnit\Framework\TestCase;

class FileResolverTest extends TestCase
{
    public function testShouldThrowExceptionWhenFileIsNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("File not found");

        $fr = new FileResolver(null);
        $fr->getFilePath();
    }

    public function testShouldReturnAbsolutePath()
    {
        $fr = new FileResolver(__DIR__ . '/../configtest.yml');
        $this->assertEquals(__DIR__ . '/../configtest.yml', $fr->getFilePath());
    }

    public function testShouldReturnRelativePath()
    {
        chdir(__DIR__);
        $fr = new FileResolver('../configtest.yml');
        $this->assertEquals(__DIR__ . '/../configtest.yml', $fr->getFilePath());
    }
}
