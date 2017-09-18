<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PackageIndexerTest extends TestCase {

	const db_host = "localhost";
	const db_user = "ocean-tester";
	const db_pw = "test";
	const db_name = "oceanPiTest";

	private $db_connection;

	public function __construct(){
		$this->db_connection = new DB_Connection(self::db_host, self::db_user, self::db_pw, self::db_name);

		// truncate db
		$this->db_connection->query("truncate packages");
		$this->db_connection->query("truncate package_dependencies");
		parent::__construct();
	}

	public function testAddPackageSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->add_package("testPackage", []);

		$this->assertEquals("OK",$result);
	}

	public function testAddPackageSuccess2(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->add_package("testPackage2", ["testPackage"]);

		$this->assertEquals("OK",$result);
	}

	public function testAddPackageFailure(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->add_package("testPackage3", ["a","b"]);

		$this->assertEquals("FAIL",$result);
	}

	public function testAddIndexSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->add_index("testPackage4");

		$this->assertTrue(is_numeric($result));
	}

	public function testClearDependenciesSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->clear_dependencies(["id" => 1]);

		$this->assertTrue($result);
	}

	public function testGetIndexedPackagesSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$results = $PI->get_indexed_packages(["testPackage"]);

		$this->assertTrue(isset($results[0]['id']));
	}

	public function testRemovePackageFailure(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->remove_package("testPackage");

		$this->assertEquals("FAIL", $result);
	}

	public function testRemovePackageSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->remove_package("testPackage2");

		$this->assertEquals("OK", $result);
	}

	public function testQueryPackageSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->query_package("testPackage");

		$this->assertEquals("OK", $result);
	}

	public function testQueryPackageFailure(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->query_package("testPackage2");

		$this->assertEquals("FAIL", $result);
	}

	public function testGetPackageByNameSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->get_package_by_name("testPackage");

		$this->assertTrue(isset($result['id']));
	}

	public function testGetPackageByNameFailure(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->get_package_by_name("notpresent");

		$this->assertFalse(isset($result['id']));
	}

	public function testAddDependenciesSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->add_dependencies(1, []);

		$this->assertTrue($result);
	}

	public function testRemoveDependenciesSuccess(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->remove_dependencies(99);

		$this->assertTrue($result);
	}

	public function testIsDependencyFailure(){
		$PI = new PackageIndexer($this->db_connection);
		$result = $PI->is_dependency("testPackage");

		$this->assertFalse($result);
	}
}

