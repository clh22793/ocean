<?php
require_once('db_connection.php');
require_once('package_indexer.php');

class IndexServer {

	const TIMEOUT = 60 * 60;
	private $socket;
	private $host;
	private $port;
	private $max_threads;

	private $PI;

	public function __construct($socket, $pi){
		$this->socket = $socket;
		$this->PI = $pi;
	}

	public function __destruct(){
		//fclose($this->socket);
	}

	public function create_socket_server(){
		$this->socket = stream_socket_server('tcp://'.$this->host.':'.$this->port, $errno, $errstr);
		if (!$this->socket){
			echo "$errstr ($errno)<br />\n";
			exit;
		}
	}

	public function start($db_host, $db_user, $db_pw, $db_name){
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

	public function process_message($message){
print "message: $message";
		//while(true) {
//			$conn = stream_socket_accept($this->socket, self::TIMEOUT);
//			while($message = fread($conn, 1024)) {
				$command = $this->get_command($message);
				$package_name = $this->get_package_name($message);
				$dependencies = $this->get_dependencies($message);

				if(!$command){
					$response = "ERROR";
					//stream_socket_sendto($conn, "ERROR\n");
					//continue;
				} else if(!$package_name){
					$response = "ERROR";
					//stream_socket_sendto($conn, "ERROR\n");
					//continue;
				} else if($command == "INDEX"){
					$response = $this->PI->add_package($package_name, $dependencies);
					//stream_socket_sendto($conn, $response."\n");
				} else if($command == "REMOVE"){
					$response = $this->PI->remove_package($package_name);
					//stream_socket_sendto($conn, $response."\n");

				} else if ($command == "QUERY") {
					$response = $this->PI->query_package($package_name);
					//stream_socket_sendto($conn, $response."\n");
				} else {
					$response = "OK";
					
					//stream_socket_sendto($conn, "OK\n");
				}

				return $response;
//			}
//			fclose($conn);
		//}
	}

}




