<?php

namespace core\mem;

use core\proc\Process;

class Msgqueue {

	public static function push($pid, $msg)
	{
		$shm_data = Process::$shm->readShared();
		if (!isset($shm_data[$pid])) {
			$shm_data[$pid] = array();
		}
		$shm_data[$pid] = $msg;
		return Process::$shm->writeShared($shm_data);
	}

	public static function shift($pid)
	{
		$shm_data = Process::$shm->readShared();
		if (!isset($shm_data[$pid]) || empty($shm_data[$pid])) {
			return false;
		}
		$msg = array_shift($shm_data[$pid]);
		Process::$shm->writeShared($shm_data);
		return $msg;
	}

	public static function delQueue($pid)
	{
		$shm_data = Process::$shm->readShared();
		if (isset($shm_data[$pid])) {
			unset($shm_data[$pid]);
		}
		return Process::$shm->writeShared($shm_data);
	}
}