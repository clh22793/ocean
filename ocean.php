<?php 

class DB_Connection {
	private $resource;

	public function __construct($host, $user, $pw, $db){
		$this->resource = mysqli_connect($host, $user, $pw, $db);
	}

	public function real_escape_string($string){
		return $this->resource->real_escape_string($string);
	}

	public function query($query){
		return $this->resource->query($query);
	}

}

class PackageIndexer {

	private $db_connection;

	public function __construct($db_connection){
		$this->db_connection = $db_connection;
	}

	public  function add_package($package, array $dependencies = []){
		$dependency_records = $this->is_indexed($dependencies);
		if(!empty($dependencies) && !$dependency_records){
			throw new Exception("All dependencies not indexed.");
		}

		if($package_records = $this->is_indexed[$package]){
			// clear dependencies
			$this->clear_dependencies($package_records[0]);
		}

		$package_record = $this->add_index($package);
		$this->add_dependencies($package_record, $dependency_records);

	}

	public function add_index($name){
		$name = $this->db_connection->real_escape_string($name);
		$active = 1;
		$result = $this->db_connection->query("select * from packages where name = '{$name}' and active = {$active}");

		if($result->num_rows > 0){
			return $result->fetch_assoc();
		} else {
			$result = $this->db_connection->query("insert into packages (name) values ({'$name}')");
			return $result->fetch_assoc();
		}

	}

	public function clear_dependencies($package){
		$package_id = $package['id'];
		$result = $this->db_connection->query("update package_dependencies set active=0 where package_id = {$package_id}");
	}

	public  function is_indexed(array $packages=[]){
		// return true if $packages are indexed
		foreach($packages as $package){
			$package = $this->db_connection->real_escape_string($package);
			$result = $this->db_connection->query("select * from packages where name = '{$package}'");
			if($result->num_rows == 0){
				return false;
			}
		}
	
		while($row = $result->fetch_assoc()){
			$results[] = $row;
		}

		return $results;
	}

	public  function remove($name){
		// if $name is a dependency, throw error
		if($this->is_dependency($name)){
			throw new Exception("cannot remove an active dependency");
		// else, remove $name from index
		} else if ($record = $this->get_package_by_name($name)){
			$active = 0;
			$package_id = $record['id'];
			$result = $this->db_connection->query("update packages set active={$active} where id = {$package_id}");
		}

	}

	public  function query($name){
		// if $name is index, return OK
		if($records = $this->is_indexed[$name]){
			return $records;
		// else, throw error
		} else {
			throw new Exception("package is not indexed");
		}

	}

	public function get_package_by_name($name){
		$name = $this->db_connection->real_escape_string($name);
		$result = $this->db_connection->query("select * from packages where name = '{$name}'");

		if($result->num_rows == 0){
			return false;
		} else {
			return $result->fetch_assoc();
		}
	}

	public  function add_dependencies(array $package_record, array $dependency_records){
		// void existing dependencies
		$this->remove_dependencies($package_record[0]['id']);
	
		// add new dependencies for $name package
		foreach($dependency_records as $record){
			$result = $this->db_connection->query("insert into package_dependencies (package_id, dependency_id), values({$package_record[0]['id']}, {$record['id']})");
		}
	}

	public function remove_dependencies($package_id){
		$active = 0;
		$result = $this->db_connection->query("updated package_dependencies set active={$active} where package_id = {$package_id}");
	}

	public function is_dependency($name){
		$active = 1;
		$name = $this->db_connection->real_escape_string($name);
		$result = $this->db_connection->query("select * from packages p inner join package_dependencies pd on p.id = pd.dependency_id where p.name = {$name} and pd.active = {$active}");

		return $result->num_rows;
	}
	
}


$host = "localhost";
$user = "ocean-tester";
$pw = "test";
$db = "oceanPi";

$PI = new PackageIndexer(new DB_Connection($host, $user, $pw, $db));
$PI->add_package("test1");
