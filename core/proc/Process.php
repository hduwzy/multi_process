<?php
namespace core\proc;

// 进程管理
// 父子进程通信
// 套接字监听

abstract class Process {

	private static $_instance;
	private $_pm;

	private function __construct()
	{
		$this->_pm = new ProcessM($this);
	}

	public static function getInstance($is_master = false)
	{
		if (null === self::$_instance) {
			self::$_instance = new static();
			if ($is_master) {
				self::$_instance->getProcManager()->setAliasPid('slef', \posix_getpid());
			}
		}
		return self::$_instance;
	}

	public function setProcManager(ProcessM $pm)
	{
		$this->_pm = $pm;
	}

	public function getProcManager()
	{
		return $this->_pm;
	}

	public function forkChild($alias, $child_porc, $params = array())
	{
		return $this->_pm->forkChild($alias, $child_porc, $params);
	}

	public function monitorChildren()
	{
		$this->_pm->monitorChildren();
	}

	public function start()
	{
		$this->run();
	}

	public function getPMInfo()
	{
		return $this->_pm;
	}

	abstract public function run();
}