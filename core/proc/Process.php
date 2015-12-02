<?php

namespace core\proc;
use core\events\Libevent;
use core\events\Select;
use core\events\EventInterface;

class Process {

	public static $pid;
	public static $ppid;
	public static $child;
	public static $alias;
	public static $shm_to_pid;
	private static $shm_basic_key = 1024;
	private static $max_child_idx = 0;

	// public static $shm_to_parent;
	public static $events;
	public static $do_once = false;

	public static function init()
	{
		self::$pid = \posix_getpid();
		self::$ppid = \posix_getppid();
		self::$child = array();
		self::$alias = array();
		self::$shm_to_pid = array();
		self::$shm_basic_key *= 2;
		self::$max_child_idx = 0;

		// self::$shm_to_parent = -1;

		
		if (!self::$do_once) {
			// 初始化事件对象
			if(extension_loaded('libevent')) {
			    self::$events = new Libevent();
			} else {
			    self::$events = new Select();
			}

			// 注册用户信号SIGUSR1处理函数
			self::onSysEvent(SIGUSR1, EventInterface::EV_SIGNAL, array("\\core\\proc\\Process", 'defaultSigusr1Cbk'));
			// 注册子进程退出处理函数
			self::onSysEvent(SIGCHLD, EventInterface::EV_SIGNAL, array("\\core\\proc\\Process", 'defaultSigchldCbk'));
			// 注册用户信号SIGUSR2处理函数
			self::onSysEvent(SIGUSR2, EventInterface::EV_SIGNAL, array("\\core\\proc\\Process", 'defaultSigusr2Cbk'));

			// 注册exit回调函数
			register_shutdown_function(function(){
				Process::delShmAlloc();
			});
			self::$do_once = true;
		}
	}

	public static function fork($alias, $callback, $params = array())
	{
		$shm_id = self::shmAlloc();
		$pid = \pcntl_fork();

		if ($pid < 0) {
			exit(-1);
		} elseif ($pid == 0) {
			// child
			self::init();
			sleep(1);
			self::$shm_to_pid[self::$ppid] = $shm_id;

			call_user_func_array($callback, $params);
			exit(0);
		} elseif ($pid > 0) {
			// parent
			self::$child[] = $pid;
			self::$max_child_idx++;
			self::$alias[$alias] = $pid;
			self::$shm_to_pid[$pid] = $shm_id;
		}
	}

	public static function sendMsg($alias, $data)
	{
		
	}


	public static function postSignal($sig, $pid)
	{
		return \posix_kill($pid, $sig);
	}

	public static function registerUserEvent($ev_name, $ev_call)
	{

	}

	public static function fireUserEvent($ev_name)
	{

	}

	public static function onSysEvent($fd, $flag, $func, $args=array())
	{
		self::$events->add($fd, $flag, $func, $args);
	}

	public static function shmAlloc($size = 1024)
	{
		$shm_id = \shmop_open(self::$shm_basic_key + self::$max_child_idx, 'c', 0666, $size);
		return $shm_id;
	}

	public static function delShmAlloc()
	{
		foreach (self::$shm_to_pid as $pid => $shm_id) {
			shmop_close($shm_id);
		}
	}

	public static function defaultSigusr1Cbk($fd, $events, $args)
	{
		echo "fd:$fd\n";
		echo "events:$events\n";
	}

	public static function defaultSigusr2Cbk($fd, $events, $args)
	{
		
	}

	public static function defaultSigchldCbk($fd, $events, $args)
	{
		$status = '';
		while(($pid = \pcntl_waitpid(-1, $status)) > 0) {
			foreach (self::$child as $key => $value) {
				if ($value == $pid) {
					unset(self::$child[$key]);
					foreach (self::$alias as $key => $value) {
						if ($pid == $value) {
							unset(self::$alias[$key]);
						}
					}
				}
			}
		}
		if (count(self::$child) == 0) {
			exit;
		}
	}

	public static function loop()
	{
		self::$events->loop();
	}
}


