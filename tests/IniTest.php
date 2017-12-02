<?php

use PHLAK\Config;

class IniTest extends PHPUnit_Framework_TestCase
{
    use Initializable;

    public function setUp()
    {
        $this->validConfig = __DIR__ . '/files/ini/config.ini';
        $this->invalidConfig = __DIR__ . '/files/ini/invalid.ini';
    }
    public function test_it_can_load_typed_values()
    {
        $config = new Config\Config();

        $config->set('drivers.mysql.port', null);
        $config->load(__DIR__ . '/files/ini/config.ini', true);

        $this->assertInternalType("int", $config->get('drivers.mysql.port'));
    }
}
