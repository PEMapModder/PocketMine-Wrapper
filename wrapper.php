<?php

require_once "functions.php";

$targets = [
	"PocketMine-MP.phar",
	"src/pocketmine/PocketMine.php"
];

$args = isset($argv) ? $argv :
	console("[WARNING] Not passing any command line arguments to PocketMine server. Please enable 'register_argc_argv' in " .
	php_ini_loaded_file() . " if you want this script to pass your arguments into PocketMine.", []);
array_shift($args); // remove the leading __FILE__

foreach($targets as $target){
	if(is_file($target)){
		$ok = true;
		break;
	}
}
if(!isset($ok)){
	console("[FATAL] No PocketMine installation (source folder or .phar) found in " . getcwd() . "!");
	exit(2);
}

request_enter_num_times_restart:
console("[?] Please enter the number of times to restart the server (including the first time starting).");
console("[?] Enter 1 if you don't want the server to restart.");
console("[?] Enter 0 if you want the server to restart for infinite times.");

while(($line = nonBlockReadLine()) === null);
if(!is_numeric($line)){
	console("[ERROR] Invalid input!");
	goto request_enter_num_times_restart;
}
$restarts = (int) $line;
$restarts = ($restarts === 0) ? PHP_INT_MAX : $restarts;

while($restarts--){
	$server = proc_open(PHP_BINARY . " " . implode(" ", $args), [0 => ["pipe", "r"], 1 => ["pipe", ,"w"]], $pipes);
	if(!is_resource($server)){
		console("[ERROR] Failed to start server once :(");
		continue;
	}
	while(proc_get_status($server)["running"] === false){
		
	}
}
