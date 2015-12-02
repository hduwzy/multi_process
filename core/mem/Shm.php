<?php

namespace core\mem;

class Shm {

	private $shm_id;
	private $sem_id;
	private $size;
	public function __construct($fpath, $proj, $size=1024) {
		$key = ftok($fpath, $proj);
		$this->sem_id = sem_get($key);
		if (!$this->sem_id) {
			exit("Cant create sem...\n");
		}
		$this->shm_id = shmop_open($key, 'c', 0666, $size);
		if (!$this->shm_id) {
			exit("Cant create shm...\n");
		}
		
		$this->size = $size;
	}

	public function readShared()
	{
		sem_acquire($this->sem_id);
		$data = shmop_read($this->shm_id, 0, $this->size);
		$data = trim($data);
		$data = unserialize($data);
		return $data;
	}

	public function writeShared($data_arr)
	{
		$null_fill = str_repeat(str_repeat(' ', 10), $this->size / 10);
		shmop_write($this->shm_id, $null_fill, 0);
		$data = serialize($data_arr);
		if (strlen($data) > $this->size) {
			return false;
		}
		shmop_write($this->shm_id, $data, 0);
		sem_release($this->sem_id);
		return true;
	}

	public function close()
	{
		shmop_close($this->shm_id);
	}
}