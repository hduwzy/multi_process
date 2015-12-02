<?php

namespace core\proc;
use core\events\Libevent;
use core\events\Select;
use core\events\EventInterface;
use core\mem\Shm;
use core\mem\Msgqueue;

class Process {

	public static $pid;
	public static $ppid;
	public static $child;
	public static $alias;
	public static $shm_to_pid;
	public static $user_events;
	public static $shm;

	public static $shm_basic_id;

	// public static $shm_to_parent;
	public static $events;
	public static $do_once = false;

	public static function init()
	{
		self::$pid = \posix_getpid();
		self::$ppid = \posix_getppid();
		self::$child = array();
		self::$alias = array();
		self::$user_events = array();
		self::$shm_to_pid = array();
		

		// self::$shm_to_parent = -1;

		
		if (!self::$do_once) {
			// 初始化事件对象
			if(extension_loaded('libevent')) {
			    self::$events = new Libevent();
			} else {
			    self::$events = new Select();
			}
			self::$shm = new Shm(__FILE__, 'a');

			// 注册用户信号SIGUSR1处理函数
			self::onSysEvent(SIGUSR1, EventInterface::EV_SIGNAL, array("\\core\\proc\\Process", 'defaultSigusr1Cbk'));
			// 注册子进程退出处理函数
			self::onSysEvent(SIGCHLD, EventInterface::EV_SIGNAL, array("\\core\\proc\\Process", 'defaultSigchldCbk'));
			// 注册用户信号SIGUSR2处理函数
			self::onSysEvent(SIGUSR2, EventInterface::EV_SIGNAL, array("\\core\\proc\\Process", 'defaultSigusr2Cbk'));

			// 注册exit回调函数
			register_shutdown_function(function(){
				Process::closeShm();
			});
			self::$do_once = true;
		}
	}

	public static function fork($alias, $callback, $params = array())
	{
		
		$pid = \pcntl_fork();

		if ($pid < 0) {
			exit(-1);
		} elseif ($pid == 0) {
			// child
			self::init();
			sleep(1);
			call_user_func_array($callback, $params);
			exit(0);
		} elseif ($pid > 0) {
			// parent
			self::$child[] = $pid;
			self::$alias[$alias] = $pid;
		}
	}

	public static function sendMsg($alia, $msg)
	{
		if (!isset(self::$alias[$alia])) {
			return false;
		}
		$pid = self::$alias[$alia];
		Msgqueue::push($pid, $msg);
		self::postSignal(SIGUSR1, $pid);
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


	public static function defaultSigusr1Cbk($fd, $events, $args)
	{
		while(($msg = Msgqueue::shift(self::$pid))) {
			var_dump($msg);
		}
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
			Msgqueue::delQueue($pid);
		}
		if (count(self::$child) == 0) {
			exit;
		}
	}

	public static function loop()
	{
		self::$events->loop();
	}

	public static function closeShm()
	{
		file_put_contents("a.txt", 'data111111');
		self::$shm->close();
	}
}


