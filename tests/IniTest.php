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

        // verify we got an expected value from the root INI file
        $this->assertEquals('admin@emailserver.net', $config->get('alerts.email'));

        // verify we got an expected value from the child INI file
        $this->assertEquals('localhost', $config->get('drivers.mysql.host'));

        // verify overwrite worked as expected
        $this->assertEquals('overrideme', $config->get('drivers.mysql.password'));
    }

    public function test_it_can_autoload_additional_files_based_on_key_with_override()
    {
        $config = new Config\Config();

        $config->set('drivers.mysql.password', 'overrideme');
        $config->load(__DIR__ . '/files/ini/config_imports.ini', true);

        // verify we got an expected value from the root INI file
	    $this->assertEquals('admin@emailserver.net', $config->get('alerts.email'));

        // verify we got an expected value from the child INI file
        $this->assertEquals('localhost', $config->get('drivers.mysql.host'));

        // verify overwrite worked as expected
        $this->assertEquals('hunter2', $config->get('drivers.mysql.password'));
    }

	public function test_it_can_load_typed_values()
	{
		$config = new Config\Config();

		$config->set('drivers.mysql.port', null);
		$config->load(__DIR__ . '/files/ini/config.ini', true);

		$this->assertInternalType("int", $config->get('drivers.mysql.port'));
	}

	public function test_it_can_load_dot_notation()
	{
		$config = new Config\Config();

		$config->load(__DIR__ . '/files/ini/config.ini', true);

		$this->assertArraySubset(['email' => ['server' => ['smtp' => [ 'host' => 'tls://smpt.emailserver.netcom:587', 'smtpdebug' => 0]]]], $config->getAll());
	}
}
