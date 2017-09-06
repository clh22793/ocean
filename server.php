<?php

// creating the socket...
$ipServer = "localhost";
$portNumber = 8080;
$timeout = 60 * 60;


$socket = stream_socket_server('tcp://'.$ipServer.':'.$portNumber, $errno, $errstr);
if (!$socket){
	echo "$errstr ($errno)<br />\n";
} else {
	// while there is connection, i'll receive it... if I didn't receive a message within $nbSecondsIdle seconds, the following function will stop.

while(true) {
		$conn = stream_socket_accept($socket, $timeout);
	while($message = fread($conn, 1024)) {
		//var_dump($conn);
			//$message= fread($conn, 1024);
			echo 'I have received that : '.$message;
			stream_socket_sendto($conn, "OK\n");
/*		if ($conn = stream_socket_accept($socket, $timeout)) {
			$message= fread($conn, 1024);
			echo 'I have received that : '.$message;
			stream_socket_sendto($conn, "OK\n");


//			fwrite ($conn, "OK\n");
			fclose ($conn);
		}
*/
	//	fclose($conn);
		//fclose($socket);
	}
		fclose($conn);
}
}

