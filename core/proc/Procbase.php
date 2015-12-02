<?php

namespace core\proc;

class Procbase {

	private $pid;
	private $ppid;
	private $child_procs = array();
	private $closure;
	private $params;
	
	public function __construct()
	{

	}

	public function setPid($pid)
	{

	}

	public function getPid()
	{

	}

	public function setPPid($ppid)
	{

	}

	public function getPPid()
	{

	}

	public function start()
	{
		$this->run();
	}

	public function run($closure = null, $params = null)
	{

	}
}
