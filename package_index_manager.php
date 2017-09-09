<?php
require_once('index_server.php');

class PackageIndexManager {
	const TIMEOUT = 600;

	public function __construct($socket_host, $socket_port){
		$this->socket = stream_socket_server('tcp://'.$socket_host.':'.$socket_port, $errno, $errstr);
		if (!$this->socket){
			echo "$errstr ($errno)<br />\n";
			exit;
		}

		$this->config = parse_ini_file("config.ini");
	}

	public function start($socket_max_threads, $max_idle_time, $waitpid=false){
		$process_ids = [];
		for($i=0; $i < $socket_max_threads; $i++){
			$pid = pcntl_fork();
			$process_ids[] = $pid;
/*			if($pid && $waitpid){
				// this is the parent process
				// wait until the child has finished processing then end the script
				pcntl_waitpid($pid, $status, WUNTRACED);
				exit;
			} else*/ if(!$pid){
				$start_time = time();
				$idle_time = 0;
				$server = new IndexServer($this->socket, new PackageIndexer(new DB_Connection($this->config['db_host'], $this->config['db_user'], $this->config['db_pw'], $this->config['db_name'])));

//				while($idle_time < $max_idle_time){
				while(true){
					$conn = stream_socket_accept($this->socket, self::TIMEOUT);
					$idle_time = time() - $start_time;
					while($conn && $message = fread($conn, 1024)) {
						$response = $server->process_message($message);		
						stream_socket_sendto($conn, $response."\n");
						$idle_time = 0;
					}
				}

				exit($i);
			}
		}

		while (pcntl_waitpid(0, $status) != -1) {
			$status = pcntl_wexitstatus($status);
			echo "Child $status completed\n";
		}

		return $process_ids;

	}
}

