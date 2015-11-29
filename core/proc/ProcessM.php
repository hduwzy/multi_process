<?php
namespace core\proc;

class ProcessM {

	private $_child_procs = array();
	private $_alias_pid = array();
	private $_self;

	private $_pid;
	private $_ppid;

	public function __construct(Process $self)
	{
		$this->_self = $self;
	}

	public function setPid($pid)
	{
		$this->_pid = $pid;
	}

	public function getPid()
	{
		if (null === $this->_ppid) {
			return \posix_getpid();
		} else {
			return $this->_pid;
		}
	}

	public function setPPid($ppid)
	{
		$this->_ppid = $ppid;
	}

	public function getPPid()
	{
		if (null === $this->_ppid) {
			return -1;
		} else {
			return $this->_ppid;
		}
	}

	public function resetChildren()
	{
		$this->_child_procs = array();
	}

	public function setChildProcs($alias, $proc)
	{

	}

	public function setAliasPid($alias, $pid)
	{
		$this->_alias_pid[$alias] = $pid;
	}

	public function getPidByAlias($alias)
	{
		if (isset($this->_alias_pid[$alias])) {
			return $this->_alias_pid[$alias];
		}
		return -1;
	}

	public function getAliasByPid($pid) {
		$temp = array_flip($this->_alias_pid);
		if (isset($temp[$pid])) {
			return $temp[$pid];
		}
		return false;
	}

	public function monitorChildren()
	{
		$status = '';
		while(($pid = pcntl_waitpid(-1, $status, WNOHANG))) {
			echo "pid:$pid exit\n";
			$alias = $this->getAliasByPid($pid);
			unset($this->_alias_pid[$alias]);
			if (count($this->_alias_pid) < 1 && isset($this->_alias_pid['self'])) {
				exit();
			}
		}
	}

	public function forkChild($alias, $child_proc, $params = array())
	{
		if ($this->getAliasByPid($alias) > 0) {
			return false;
		}

		if (class_exists($child_proc)) {
			$child_proc = call_user_func(array($child_proc, 'getInstance'));
			if (null === $child_proc) {
				return false;
			}
		}
		
		$pid = pcntl_fork();
		
		if ($pid == 0) {
			$child_proc->getProcManager()->setAliasPid('self', \posix_getpid());
			$child_proc->getProcManager()->setAliasPid('parent', \posix_getppid());
			call_user_func_array(array($child_proc, 'start'), $params);
		} elseif ($pid == -1) {
			exit(-1);
		} elseif ($pid > 0) {
			$this->setAliasPid($alias, $pid);
			return true;
		}

		return false;
	}

	public function killChild()
	{

	}
}