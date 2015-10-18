<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 */

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
class CommandReader extends Thread{
	public static $os;
	private $readline;
	/** @var \Threaded */
	protected $buffer;
	public function __construct(){
		$this->buffer = \ThreadedFactory::create();
		$this->start();
	}
	private function readLine(){
		if(!$this->readline){
			$line = trim(fgets(fopen("php://stdin", "r")));
		}else{
			$line = trim(readline("> "));
			if($line != ""){
				readline_add_history($line);
			}
		}
		return $line;
	}
	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}
		return null;
	}
	public function run(){
		$opts = getopt("", ["disable-readline"]);
		if(extension_loaded("readline") and !isset($opts["disable-readline"])){
			$this->readline = true;
		}else{
			$this->readline = false;
		}
		$lastLine = microtime(true);
		while(true){
			if(($line = $this->readLine()) !== ""){
				$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			}elseif((microtime(true) - $lastLine) <= 0.1){ //Non blocking! Sleep to save CPU
				usleep(40000);
			}
			$lastLine = microtime(true);
		}
	}
	public function getThreadName(){
		return "Console";
	}
	public static function getOS($recalculate = false){
		if(self::$os === null or $recalculate){
			$uname = php_uname("s");
			if(stripos($uname, "Darwin") !== false){
				if(strpos(php_uname("m"), "iP") === 0){
					self::$os = "ios";
				}else{
					self::$os = "mac";
				}
			}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
				self::$os = "win";
			}elseif(stripos($uname, "Linux") !== false){
				if(@file_exists("/system/build.prop")){
					self::$os = "android";
				}else{
					self::$os = "linux";
				}
			}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
				self::$os = "bsd";
			}else{
				self::$os = "other";
			}
		}

		return self::$os;
	}
}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
class ThreadedFactory{
	/** @var ThreadedFactory */
	protected static $instance;
	/** @var \Threaded[] */
	protected $threadedList = [];

	protected function __construct(){
		global $threadedFactoryInstance;
		$threadedFactoryInstance = $this;
		self::$instance = $this;
	}

	/**
	 * @return ThreadedFactory
	 */
	public static function getInstance(){
		if(self::$instance === null){
			global $threadedFactoryInstance;
			if($threadedFactoryInstance instanceof ThreadedFactory){
				self::$instance = $threadedFactoryInstance;
			}else{
				new ThreadedFactory();
			}
		}

		return self::$instance;
	}

	/**
	 * @param \Threaded $class
	 * @param ...$arguments
	 *
	 * @return \Threaded
	 */
	public static function create($class = \Threaded::class, ...$arguments){
		/** @var \Threaded $threaded */
		$threaded = new $class(...$arguments);
		self::getInstance()->threadedList[spl_object_hash($threaded)] = $threaded;
		return $threaded;
	}

	public static function destroy(\Threaded $threaded){
		$instance = self::getInstance();
		if(isset($instance->threadedList[$hash = spl_object_hash($threaded)])){
			$threaded->synchronized(function(\Threaded $t){
				$t->notify();
			}, $threaded);
			unset($instance->threadedList[$hash]);
			return true;
		}
		return false;
	}

	/**
	 * @return \Threaded[]
	 */
	public static function all(){
		return self::getInstance()->threadedList;
	}
}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
class StreamReader extends Thread{
	public static $os;
	/** @var \Threaded */
	protected $buffer;
	private $res;
	public $running = true;
	public function __construct($resource){
		$this->buffer = \ThreadedFactory::create();
		$this->res = $resource;
		$this->start();
	}
	private function readLine(){
		$line = rtrim(fgets($this->res), "\r\n");
		return $line;
	}
	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}
		return null;
	}
	public function run(){
		$lastLine = microtime(true);
		while($this->running){
			if(($line = $this->readLine()) !== ""){
				$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			}elseif((microtime(true) - $lastLine) <= 0.1){ //Non blocking! Sleep to save CPU
				usleep(40000);
			}
			$lastLine = microtime(true);
		}
	}
	public function getThreadName(){
		return "Console";
	}
	public static function getOS($recalculate = false){
		if(self::$os === null or $recalculate){
			$uname = php_uname("s");
			if(stripos($uname, "Darwin") !== false){
				if(strpos(php_uname("m"), "iP") === 0){
					self::$os = "ios";
				}else{
					self::$os = "mac";
				}
			}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
				self::$os = "win";
			}elseif(stripos($uname, "Linux") !== false){
				if(@file_exists("/system/build.prop")){
					self::$os = "android";
				}else{
					self::$os = "linux";
				}
			}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
				self::$os = "bsd";
			}else{
				self::$os = "other";
			}
		}

		return self::$os;
	}
}

function kill($pid){
	switch(CommandReader::getOS()){
		case "win":
			exec("taskkill.exe /F /PID " . ((int) $pid) . " > NUL");
			break;
		case "mac":
		case "linux":
		default:
			exec("kill -9 " . ((int) $pid) . " > /dev/null 2>&1");
	}
}

function console($msg, $ret = null, $eol = true){
	echo date("[H:i:s] ") . $msg . ($eol ? PHP_EOL : "");
	return $ret;
}

$cmdReader = new CommandReader();

function nonBlockReadLine(){
	global $cmdReader;
	return $cmdReader->getLine();
}
