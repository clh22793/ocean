<?php
require_once('index_server.php');

$socket_host = "localhost";
$socket_port = 8080;
$socket_max_threads = 10;

$db_host = "localhost";
$db_user = "ocean-tester";
$db_pw = "test";
$db_name = "oceanPi";

$server = new IndexServer($socket_host, $socket_port, $socket_max_threads);
$server->start($db_host, $db_user, $db_pw, $db_name);




