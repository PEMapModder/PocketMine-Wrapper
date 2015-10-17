<?php

require_once "functions.php";

$targets = [
	"PocketMine-MP.phar",
	"src/pocketmine/PocketMine.php"
];

foreach($targets as $target){
	if(is_file($target)){
		$ok = true;
		break;
	}
}
if(!isset($ok, $target)){
	console("[FATAL] No PocketMine installation (source folder or .phar) found in " . getcwd() . "!");
	exit(2);
}

$args = isset($argv) ? $argv :
	console("[WARNING] Not passing any command line arguments to PocketMine server. Please enable 'register_argc_argv' in " .
	php_ini_loaded_file() . " if you want this script to pass your arguments into PocketMine.", []);
if(is_file($args[0]) and realpath($args[0]) === realpath(__FILE__)){
	array_shift($args);
}
array_unshift($args, $target);

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
	console("Initializing server...");
	$server = proc_open(PHP_BINARY . " " . implode(" ", $args), [0 => ["pipe", "r"], 1 => ["pipe", ,"w"], 2 => fopen("php://stderr", "wb")], $pipes);
	if(!is_resource($server)){
		console("[ERROR] Failed to start server once :(");
		continue;
	}
	console("[*] The server has been started. Type '-die' to stop the server and prevent this script from restarting. Type '-kill' to terminate the server and stop this script.");
	while(proc_get_status($server)["running"]){
		echo stream_get_contents($pipes[1]);
		while(($line = nonBlockReadLine()) !== null){
			if(strtolower($line) === "-die"){
				$restarts = 0;
				$line = "stop";
			}elseif(strtolower($line) === "-kill"){
				fclose($pipes[0]);
				fclose($pipes[1]);
				if(isset($pipes[2])){
					fclose($pipes[2]);
				}
				$pid = proc_get_status($server)["pid"];
				proc_terminate($server);
				switch(CommandReader::getOS()){
					case "win":
						$tasks = `umic process where (ParentProcessId=$pid) get ProcessId`;
						foreach(explode(PHP_EOL, $tasks) as $child){
							if(is_numeric($child)) kill($child);
						}
						break;
					default:
						exec("kill -9 -\$(ps -o pgid= $pid | grep -o '[0-9]*')");
						break;
				}
				proc_close($server);
				exit;
			}
			fwrite($pipes[0], $line . PHP_EOL);
		}
	}
	echo stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	fclose($pipes[0]);
	if(isset($pipes[2])){ // I am not sure if it exists
		fclose($pipes[2]);
	}
	proc_close($pipes[2]);
	console("[*] The server has been stopped.");
}
