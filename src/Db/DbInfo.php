<?php

namespace Lyx\Db;

class DbInfo
{
	public $driver;
	public $host;
	public $port;
	public $unix_socket;
	public $dbname;
	public $username;
	public $password;

	public function __construct()
	{
		if(func_num_args() > 0)
			call_user_func_array(array($this, 'set'), func_get_args());
	}
	
	public function set()
	{
		$this->reset();
		$argv = func_get_args();
		$argc = func_num_args();
		
		if($argc == 1) { // dsn | array
			if(is_string($argv[0]))
				$this->parseDsn($argv[0]);
			elseif(is_array($argv[0]) || (is_object($argv[0]) /*&& get_class($argv[0]) == __CLASS__*/))
				$this->loadFromKeys($argv[0]);
		} elseif($argc == 3) { // dsn, username, password
			$this->parseDsn($argv[0]);
			$this->username = $argv[1];
			$this->password = $argv[2];
		} elseif($argc == 4) { // host, dbname, username, password
			$this->host = $argv[0];
			$this->dbname = $argv[1];
			$this->username = $argv[2];
			$this->password = $argv[3];
		}
	}
	
	public function reset()
	{
		$this->driver = '';
		$this->host = '';
		$this->port = 0;
		$this->unix_socket = '';
		$this->dbname = '';
		$this->username = '';
		$this->password = '';
	}

	public function loadFromKeys($arr)
	{
		foreach($arr as $name => $value) {
			if($name == 'dsn')
				$this->parseDsn($value);
			elseif(property_exists($this, $name))
				$this->{$name} = $value;
		}
	}

	public function parseDsn($dsn)
	{
		try {
			list($this->driver, $drvdata) = explode(':', $dsn);
			$drvparams = explode(';', $drvdata);
				
			foreach($drvparams as $param) {
				list($name, $value) = explode('=', $param);
				if(property_exists($this, $name))
					$this->{$name} = $value;
			}
				
			return true;
		} catch(Exception $e) {
			return false;
		}
	}

	public function compilePdoDsn($addCredentials = false, $defaultDriver = 'mysql')
	{
		if(strlen($this->driver) == 0)
			$drv = $defaultDriver;
		else
			$drv = $this->driver;

		$dsn = "{$drv}:";

		if(!empty($this->host)) $dsn .= "host={$this->host};";
		if(!empty($this->port)) $dsn .= "port={$this->port};";
		if(!empty($this->unix_socket)) $dsn .= "unix_socket={$this->unix_socket};";
		if(!empty($this->dbname)) $dsn .= "dbname={$this->dbname};";

		if($addCredentials) {
			if(!empty($this->username)) $dsn .= "username={$this->username};";
			if(!empty($this->password)) $dsn .= "password={$this->password};";
		}

		if(substr($dsn, -1) == ';')
			$dsn = substr($dsn, 0, -1);

		return $dsn;
	}
}
