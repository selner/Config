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

    public function test_it_can_autoload_additional_files_based_on_key_without_override()
    {
        $config = new Config\Config();

        $config->set('drivers.mysql.password', 'overrideme');
        $config->load(__DIR__ . '/files/ini/config_imports.ini', false);

        $this->assertEquals('overrideme', $config->get('drivers.mysql.password'));
    }

    public function test_it_can_autoload_additional_files_based_on_key_with_override()
    {
        $config = new Config\Config();

        $config->set('drivers.mysql.password', 'overrideme');
        $config->load(__DIR__ . '/files/ini/config_imports.ini', true);

        $this->assertEquals('farmer1', $config->get('drivers.mysql.password'));
    }

    public function test_it_can_load_typed_values()
    {
        $config = new Config\Config();

        $config->set('drivers.mysql.port', null);
        $config->load(__DIR__ . '/files/ini/config.ini', true);

        $this->assertInternalType("int", $config->get('drivers.mysql.port'));
    }
}
