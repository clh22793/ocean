<?php
require_once('ocean.php');

$host = "localhost";
$user = "ocean-tester";
$pw = "test";
$db = "oceanPi";

$PI = new PackageIndexer(new DB_Connection($host, $user, $pw, $db));

// creating the socket...
$ipServer = "localhost";
$portNumber = 8080;
$timeout = 60 * 60;

$socket = stream_socket_server('tcp://'.$ipServer.':'.$portNumber, $errno, $errstr);
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
/*
			$message = trim($message);

			$message_parts = explode("|", $message);
			$command = $message_parts[0];
			$package = $message_parts[1];
			$dependencies = (!empty($message_parts[2])) ? explode(",", $message_parts[2]) : [];

			if(!validate_command($command)){
print "\n invalid command \n";
				stream_socket_sendto($conn, "ERROR\n");
				continue;
			}
			
			if($command == "INDEX" && !validate_package($package)){
print "\n invalid package \n";
				stream_socket_sendto($conn, "ERROR\n");
				continue;
			}

			stream_socket_sendto($conn, "OK\n");
*/
/*
			if(validate_command($command)){
				if(validate_package($package)){

					if($command == "INDEX"){
						//print "adding $package";
						//print_r($dependencies);
						//print_r($message_parts);
						$response = $PI->add_package($package, $dependencies);

						stream_socket_sendto($conn, $response."\n");
					} else {
						stream_socket_sendto($conn, "OK\n");
					}	

				} else {
					stream_socket_sendto($conn, "ERROR\n");
				}
			} else {
				stream_socket_sendto($conn, "ERROR\n");
			}
*/

		}
		fclose($conn);
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

