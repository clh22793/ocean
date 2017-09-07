<?php
require_once('index_server.php');

class PackageIndexManager {
	const TIMEOUT = 60;

	public function __construct($socket_host, $socket_port){
		$this->socket = stream_socket_server('tcp://'.$socket_host.':'.$socket_port, $errno, $errstr);
		if (!$this->socket){
			echo "$errstr ($errno)<br />\n";
			exit;
		}

		$this->config = parse_ini_file("config.ini");
	}

	public function start($socket_max_threads, $max_idle_time){
		$process_ids = [];
		for($i=0; $i < $socket_max_threads; $i++){
			$pid = pcntl_fork();
			if($pid){
				$process_ids[] = $pid;
			} else if(!$pid){
				$start_time = time();
				$idle_time = 0;
				$server = new IndexServer($this->socket, new PackageIndexer(new DB_Connection($this->config['db_host'], $this->config['db_user'], $this->config['db_pw'], $this->config['db_name'])));

				while($idle_time < $max_idle_time){
					$conn = stream_socket_accept($this->socket, self::TIMEOUT);
					$idle_time = time() - $start_time;
					while($conn && $message = fread($conn, 1024)) {
						$response = $server->process_message($message);		
						stream_socket_sendto($conn, $response."\n");
						$idle_time = 0;
					}
				}

				exit;
			}
		}

		return $process_ids;

	}
}

