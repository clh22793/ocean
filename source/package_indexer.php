<?php 

class PackageIndexer {

	private $db_connection;

	public function __construct($db_connection){
		$this->db_connection = $db_connection;
	}

	public  function add_package($package, array $dependencies = []){
		$dependency_records = $this->get_indexed_packages($dependencies);

		if(count($dependencies) !== count($dependency_records)){
			return "FAIL";
		}

		if($package_records = $this->get_indexed_packages([$package])){
			$this->clear_dependencies($package_records[0]);
		}

		$package_id = $this->add_index($package);
		$this->add_dependencies($package_id, $dependency_records);

		return "OK";
	}

	public function add_index($name){
		$name = $this->db_connection->real_escape_string(trim($name));
		$result = $this->db_connection->query("select * from packages where name = '{$name}'");

		if($result->num_rows > 0){
			$active = 1;
			$this->db_connection->query("update packages set active = {$active} where name = '{$name}'");
			$row = $result->fetch_assoc();
			return $row['id'];
		} else {
			$result = $this->db_connection->query("insert into packages (name) values ('{$name}')");
			return mysqli_insert_id($this->db_connection->get_resource());
		}

	}

	public function clear_dependencies($package){
		$package_id = $package['id'];
		$result = $this->db_connection->query("update package_dependencies set active=0 where package_id = {$package_id}");
		return true;
	}

	public  function get_indexed_packages(array $packages=[]){
		$indexed_packages = [];

		foreach($packages as &$package){
			$package = '"' . $this->db_connection->real_escape_string(trim($package)) . '"';
		}
		
		if(!empty($packages)){
			$package_name_list = implode(",", $packages);
			$active = 1;
			$result = $this->db_connection->query("select * from packages where name in ({$package_name_list}) and active = {$active}");
			
			while($row = $result->fetch_assoc()){
				$indexed_packages[] = $row;
			}
		}

		return $indexed_packages;
	}

	public  function remove_package($name){
		$name = trim($name);

		if($this->is_dependency($name)){
			return "FAIL";
		} else if ($record = $this->get_package_by_name($name)){
			$active = 0;
			$package_id = $record['id'];
			$result = $this->db_connection->query("update packages set active={$active} where id = {$package_id}");
		}

		return "OK";
	}

	public  function query_package($name){
		$records = $this->get_indexed_packages([trim($name)]);

		if(empty($records)){
			return "FAIL";
		} else {
			return "OK";
		}
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

		return true;
	}

	public function remove_dependencies($package_id){
		$active = 0;
		$result = $this->db_connection->query("update package_dependencies set active={$active} where package_id = {$package_id}");
		return true;
	}

	public function is_dependency($name){
		$active = 1;
		$name = $this->db_connection->real_escape_string(trim($name));
		$result = $this->db_connection->query("select * from packages p1 inner join package_dependencies pd on p1.id = pd.dependency_id inner join packages p2 on p2.id = pd.package_id where p1.name = '{$name}' and pd.active = {$active} and p2.active = {$active}");

		if(isset($result->num_rows) && $result->num_rows > 0){
			return true;
		} else {
			return false;
		}
	}
	
}

