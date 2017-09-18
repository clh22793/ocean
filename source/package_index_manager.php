<?php
require_once('index_server.php');

class PackageIndexManager {
	const TIMEOUT = 200;

	public function __construct($config){
		$this->config = $config;
		$this->socket = stream_socket_server('tcp://'.$this->config['socket_host'].':'.$this->config['socket_port'], $errno, $errstr);
		if (!$this->socket){
			echo "$errstr ($errno)<br />\n";
			exit;
		}

	}

	public function start($socket_max_threads, $keep_parent_alive=false){
		$process_ids = [];
		for($i=0; $i < $socket_max_threads; $i++){
			$pid = pcntl_fork();
			$process_ids[] = $pid;
			if(!$pid){
				$start_time = time();
				$idle_time = 0;
				$server = new IndexServer($this->socket, new PackageIndexer(new DB_Connection($this->config['db_host'], $this->config['db_user'], $this->config['db_pw'], $this->config['db_name'])));

				while(true){
					$conn = stream_socket_accept($this->socket, self::TIMEOUT);
					$idle_time = time() - $start_time;
					while($conn && $message = stream_socket_recvfrom($conn, 1500)) {
						$response = $server->process_message($message);		
						stream_socket_sendto($conn, $response."\n");
						$idle_time = 0;
					}
				}

				exit($i);
			}
		}

		if($keep_parent_alive == true){
			while (pcntl_waitpid(0, $status) != -1) {
				$status = pcntl_wexitstatus($status);
				echo "Child $status completed\n";
			}
		}

		return $process_ids;

	}
}

