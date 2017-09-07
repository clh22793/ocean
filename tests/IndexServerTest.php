<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class IndexServerTest extends TestCase {

	const db_host = "localhost";
	const db_user = "ocean-tester";
	const db_pw = "test";
	const db_name = "oceanPi";

	const server_host = "localhost";
	const server_port = 8080;
	const server_max_threads = 1;

	private $db_connection;

	public function __construct(){
		$this->server = new IndexServer(self::server_host, self::server_port, self::server_max_threads);
		parent::__construct();
	}

	public function testValidateCommandSuccess(){
		$result = $this->server->validate_command("INDEX");

		$this->assertEquals("INDEX",$result);
	}

	public function testValidateCommandFailure(){
		$result = $this->server->validate_command("notpresent");

		$this->assertFalse($result);
	}

	public function testGetCommandSuccess(){
		$result = $this->server->get_command("INDEX");

		$this->assertEquals("INDEX", $result);
	}

	public function testGetPackageNameSuccess(){
		$result = $this->server->get_package_name("INDEX|testpackage|");

		$this->assertEquals("testpackage", $result);
	}

	public function testGetPackageNameFailure(){
		$result = $this->server->get_package_name("INDEX|test bad package|");

		$this->assertFalse($result);
	}

	public function testGetDependenciesSuccess(){
		$result = $this->server->get_dependencies("INDEX|testpackage|a,b,c\n");

		$this->assertEquals("a", $result[0]);
		$this->assertEquals("b", $result[1]);
		$this->assertEquals("c", $result[2]);
	}

	public function testGetDependenciesFailure(){
		$result = $this->server->get_dependencies("INDEX|testpackage|a,b,c");

		$this->assertTrue(empty($result));
	}
}

