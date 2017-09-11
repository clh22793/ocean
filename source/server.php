<?php
require_once('package_index_manager.php');
$config = parse_ini_file("config.ini");

$socket_host = "0.0.0.0";
$socket_port = 8080;
$socket_max_threads = 100;
$max_idle_time = 600 * 5;

$man = new PackageIndexManager($config['socket_host'], $config['socket_port']);
$man->start($config['max_threads'], true);
