<?php 

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
/*
		echo "\n\n";
		echo $query."\n";
		echo $this->resource->error;
*/
		return $result;

	}

}

