<?php
require_once('index_server.php');

$timeout = 60;

$socket_host = "localhost";
$socket_port = 8080;
$socket_max_threads = 10;

$db_host = "localhost";
$db_user = "ocean-tester";
$db_pw = "test";
$db_name = "oceanPi";

$socket = stream_socket_server('tcp://'.$socket_host.':'.$socket_port, $errno, $errstr);
if (!$socket){
	echo "$errstr ($errno)<br />\n";
	exit;
}

for($i=0; $i < $socket_max_threads; $i++){
    $pid = pcntl_fork();
    if(!$pid){
		$start_time = time();
		$max_idle_time = 60 * 5;
		$idle_time = 0;
		$server = new IndexServer($socket, new PackageIndexer(new DB_Connection($db_host, $db_user, $db_pw, $db_name)));

		while($idle_time < $max_idle_time){
			$conn = stream_socket_accept($socket, $timeout);
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


