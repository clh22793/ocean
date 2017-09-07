<?php
require_once('db_connection.php');
require_once('package_indexer.php');

class IndexServer {

	const TIMEOUT = 60 * 60;
	private $socket;
	private $host;
	private $port;
	private $max_threads;

	public function __construct($host, $port, $max_threads=100){
		$this->host = $host;
		$this->port = $port;
		$this->max_threads = $max_threads;
	}

	public function create_socket_server(){
		$this->socket = stream_socket_server('tcp://'.$this->host.':'.$this->port, $errno, $errstr);
		if (!$this->socket){
			echo "$errstr ($errno)<br />\n";
			exit;
		}
	}

	public function start($db_host, $db_user, $db_pw, $db_name){
		$this->create_socket_server();

		for($i=0; $i < $this->max_threads; $i++){
			$pid = pcntl_fork();

			if(!$pid){
				$this->index_packages(new PackageIndexer(new DB_Connection($db_host, $db_user, $db_pw, $db_name)));
			}
		}

	}

	public function validate_command($command){
		$commands_list = ["INDEX", "REMOVE", "QUERY"];
		
		if(!in_array($command, $commands_list)){
			return false;
		}

		return $command;
	}

	/*public function validate_package($package){
		$match = preg_match('/[\s=]/', $package);
		return !$match;
	}*/

	public function get_command($message){
		preg_match('/^[A-Z]+|/', $message, $matches);
		return $this->validate_command($matches[0]);
	}

	public function get_package_name($message){
		preg_match('/\|[a-zA-Z0-9=\-_\+]+\|/', $message, $matches);

		if(isset($matches[0])){
			return str_replace("|", "", $matches[0]);
		} else {
			return false;
		}
	}

	public function get_dependencies($message){
		preg_match('/\|[,a-zA-Z0-9=\-_\+]+\n/', $message, $matches);
		if(!empty($matches)){
			foreach($matches as &$match){
				$match = trim($match);
			}

			return str_replace("|", "", explode(",", $matches[0]));
		} else {
			return [];
		}	

	}

	public function index_packages($PI){
		while(true) {
			$conn = stream_socket_accept($this->socket, self::TIMEOUT);
			while($message = fread($conn, 1024)) {
				$command = $this->get_command($message);
				$package_name = $this->get_package_name($message);
				$dependencies = $this->get_dependencies($message);

				if(!$command){
					stream_socket_sendto($conn, "ERROR\n");
					continue;
				} else if(!$package_name){
					stream_socket_sendto($conn, "ERROR\n");
					continue;
				} else if($command == "INDEX"){
					$response = $PI->add_package($package_name, $dependencies);
					stream_socket_sendto($conn, $response."\n");
				} else if($command == "REMOVE"){
					$response = $PI->remove_package($package_name);
					stream_socket_sendto($conn, $response."\n");

				} else if ($command == "QUERY") {
					$response = $PI->query_package($package_name);
					stream_socket_sendto($conn, $response."\n");
				} else {

					stream_socket_sendto($conn, "OK\n");
				}

			}
			fclose($conn);
		}
	}

}




