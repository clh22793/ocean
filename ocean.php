<?php 

class IndexException extends Exception {
	protected $message = "package not indexed";
	protected $code = 1001;
}

class DB_Connection {
	private $resource;

	public function __construct($host, $user, $pw, $db){
		$this->resource = mysqli_connect($host, $user, $pw, $db);
		/* check connection */
		if (!$this->resource) {
			echo "Error: Unable to connect to MySQL." . PHP_EOL;
			echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			exit;
		}
	}

	public function get_resource(){
		return $this->resource;
	}

	public function real_escape_string($string){
		return $this->resource->real_escape_string($string);
	}

	public function query($query){
		$result = $this->resource->query($query);

/*		echo "\n\n";
		echo $query."\n";
		echo $this->resource->error;
*/
		return $result;

	}

}

class PackageIndexer {

	private $db_connection;

	public function __construct($db_connection){
		$this->db_connection = $db_connection;
	}

	public  function add_package($package, array $dependencies = []){
		try {
			$dependency_records = $this->get_indexed_packages($dependencies);
		} catch (IndexException $e) {
			return "FAIL";
		}

		try{
			if($package_records = $this->get_indexed_packages([$package])){
				$this->clear_dependencies($package_records[0]);
			}
		} catch (IndexException $e) {
			// do nothing
		}

		$package_id = $this->add_index($package);
		$this->add_dependencies($package_id, $dependency_records);

		return "OK";
	}

	public function add_index($name){
		$name = $this->db_connection->real_escape_string(trim($name));
		$result = $this->db_connection->query("select * from packages where name = '{$name}'");

		if($result->num_rows > 0){
			//return $result->fetch_assoc();
			$active = 1;
			$this->db_connection->query("update packages set active = {$active} where name = '{$name}'");
			$row = $result->fetch_assoc();
		//	print_r($row);
			return $row['id'];
		} else {
			$result = $this->db_connection->query("insert into packages (name) values ('{$name}')");
			return mysqli_insert_id($this->db_connection->get_resource());
			//return $this->db_connection->insert_id;
		}

	}

	public function clear_dependencies($package){
		$package_id = $package['id'];
		$result = $this->db_connection->query("update package_dependencies set active=0 where package_id = {$package_id}");
	}

	public  function get_indexed_packages(array $packages=[]){
		$indexed_packages = [];

		foreach($packages as $package){
			$package = $this->db_connection->real_escape_string(trim($package));
			$active = 1;
			$result = $this->db_connection->query("select * from packages where name = '{$package}' and active = {$active}");

			if($result->num_rows == 0){
				// todo: resolve this issue
//				throw new IndexException("could not find package ({$package})");
				throw new IndexException();
			} else {
				$indexed_packages[] = $result->fetch_assoc();
			}
		}		

		return $indexed_packages;

	}

	public  function remove_package($name){
		$name = trim($name);

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

	public  function query_package($name){
		// if $name is index, return OK
		$records = $this->get_indexed_packages([trim($name)]);
		return $records;
	}

	public function get_package_by_name($name){
		$name = $this->db_connection->real_escape_string(trim($name));
		$result = $this->db_connection->query("select * from packages where name = '{$name}'");

		if($result->num_rows == 0){
			return false;
		} else {
			return $result->fetch_assoc();
		}
	}

	public  function add_dependencies($package_id, array $dependency_records){
		// void existing dependencies
		$this->remove_dependencies($package_id);
	
		// add new dependencies for $name package
		foreach($dependency_records as $record){
			$result = $this->db_connection->query("insert into package_dependencies (package_id, dependency_id) values({$package_id}, {$record['id']})");
		}
	}

	public function remove_dependencies($package_id){
		$active = 0;
		$result = $this->db_connection->query("update package_dependencies set active={$active} where package_id = {$package_id}");
	}

	public function is_dependency($name){
		$active = 1;
		$name = $this->db_connection->real_escape_string(trim($name));
		$result = $this->db_connection->query("select * from packages p inner join package_dependencies pd on p.id = pd.dependency_id where p.name = '{$name}' and pd.active = {$active}");

		return $result->num_rows;
	}
	
}


$host = "localhost";
$user = "ocean-tester";
$pw = "test";
$db = "oceanPi";

//$PI = new PackageIndexer(new DB_Connection($host, $user, $pw, $db));
/*$PI->add_package("test1");
$PI->add_package("test2");
$PI->add_package("test3", ["test1"]);
$PI->add_package("test4", ["test1", "test3"]);
//$PI->add_package("test5", ["test1", "notpresent"]);
//$PI->remove_package("test2");
$PI->remove_package("test1");
//$PI->remove_package("notpresent");
$PI->query_package("test1");
$PI->query_package("notpresent");
*/
