<?php

use PHLAK\Config;

class DirectoryTest extends PHPUnit_Framework_TestCase
{
    public function test_it_can_initialize_a_directory()
    {
        // set a relative path variable we can use for the test files
        putenv("CONFIG_TEST_ROOT=".__DIR__);

        $config = new Config\Config(__DIR__ . '/files');

        $this->assertInstanceOf(Config\Config::class, $config);
        $this->assertEquals('mysql', $config->get('driver'));
    }
}
