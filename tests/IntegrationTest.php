<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase {

	const db_host = "localhost";
	const db_user = "ocean-tester";
	const db_pw = "test";
	const db_name = "oceanPiTest";

	const server_host = "localhost";
	const server_port = 8081;
	const server_max_threads = 1;

	private $db_connection;
	private $server;
	private $client;
	private $process_ids = [];

	public function setUp(){
		$this->manager = new PackageIndexManager(["socket_host" => self::server_host, "socket_port" => self::server_port, "db_host" => self::db_host, "db_user" => self::db_user, "db_pw" => self::db_pw, "db_name" => self::db_name]);
		$this->process_ids = $this->manager->start(self::server_max_threads);
		$this->client = stream_socket_client("tcp://".self::server_host.":".self::server_port, $errno, $errorMessage);

		if ($this->client === false) {
			throw new Exception("Failed to connect: $errorMessage");
		}

	}

	public function tearDown(){
		foreach($this->process_ids as $process_id){
			posix_kill($process_id, 15);
		}

		fclose($this->client);
	}

	public function testMessages(){
		fwrite($this->client, "INDEX|testpackage|\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("OK\n",$result);

		fwrite($this->client, "INDEX|testpackage2|testpackage\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("OK\n",$result);

		fwrite($this->client, "INDEX|testpackage3|testpackage,testpackage2\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("OK\n",$result);

		fwrite($this->client, "INDEX|testpackage\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("ERROR\n",$result);

		fwrite($this->client, "INDEX|test package2|testpackage\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("ERROR\n",$result);

		fwrite($this->client, "REMOVE|testpackage|\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("FAIL\n",$result);

		fwrite($this->client, "REMOVE|testpackage3|\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("OK\n",$result);

		fwrite($this->client, "QUERY|testpackage|\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("OK\n",$result);

		fwrite($this->client, "QUERY|testpackag3|\n");
		$result = stream_socket_recvfrom($this->client, 1500);
		$this->assertEquals("FAIL\n",$result);
	}

}

