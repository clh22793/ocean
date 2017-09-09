<?php
require_once('package_index_manager.php');

$socket_host = "0.0.0.0";
$socket_port = 8080;
$socket_max_threads = 100;
$max_idle_time = 600 * 5;

$man = new PackageIndexManager($socket_host, $socket_port);
$man->start($socket_max_threads, $max_idle_time, true);
