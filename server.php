<?php
require_once('ocean.php');

// creating the socket...
$ipServer = "localhost";
$portNumber = 8080;
$max_threads = 100;

$socket = stream_socket_server('tcp://'.$ipServer.':'.$portNumber, $errno, $errstr);


for($i=0; $i < $max_threads; $i++){
	$pid = pcntl_fork();

	if(!$pid){
		index_packages($socket);
	}
	
}


function validate_command($command){
	$commands_list = ["INDEX", "REMOVE", "QUERY"];
	
	if(!in_array($command, $commands_list)){
		return false;
	}

	return $command;
}

function validate_package($package){
	$match = preg_match('/[\s=]/', $package);
	return !$match;
}

function get_command($message){
	preg_match('/^[A-Z]+|/', $message, $matches);
	return validate_command($matches[0]);
}

function get_package_name($message){
	preg_match('/\|[a-zA-Z0-9=\-_\+]+\|/', $message, $matches);
	return str_replace("|", "", $matches[0]);
}

function get_dependencies($message){
	preg_match('/\|[,a-zA-Z0-9=\-_\+]+\n/', $message, $matches);
	if(!empty($matches)){
		return str_replace("|", "", explode(",", $matches[0]));
	} else {
		return [];
	}	

}

function index_packages($socket){
	$timeout = 60 * 60;
	$host = "localhost";
	$user = "ocean-tester";
	$pw = "test";
	$db = "oceanPi";

	$PI = new PackageIndexer(new DB_Connection($host, $user, $pw, $db));
	if (!$socket){
		echo "$errstr ($errno)<br />\n";
	} else {
		while(true) {
			$conn = stream_socket_accept($socket, $timeout);
			while($message = fread($conn, 1024)) {
				echo 'I have received that : '.$message;
				$command = get_command($message);
				$package_name = get_package_name($message);
				$dependencies = get_dependencies($message);

				if(!$command){
	print "\n\nBad command: $message\n\n";
					stream_socket_sendto($conn, "ERROR\n");
					continue;
				} else if(!$package_name){
	print "\n\nBad package: $message\n\n";
					stream_socket_sendto($conn, "ERROR\n");
					continue;
				} else if($command == "INDEX"){
					// do something
	//print_r($dependencies);
					$response = $PI->add_package($package_name, $dependencies);
					stream_socket_sendto($conn, $response."\n");
				} else if($command == "REMOVE"){
					$response = $PI->remove_package($package_name);
	//print $response."\n";
	//exit;
					stream_socket_sendto($conn, $response."\n");

				} else if ($command == "QUERY") {
					$response = $PI->query_package($package_name);
	//print $response."\n";
	//exit;
					stream_socket_sendto($conn, $response."\n");
				} else {

					stream_socket_sendto($conn, "OK\n");
				}

			}
			fclose($conn);
		}
	}

}
