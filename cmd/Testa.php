<?php

namespace cmd;

use core\proc\Process;

class Testa extends Process{

	

	public function run()
	{
		if ($this->forkChild("worker_1", '\cmd\Testb')) {
			echo "success fork\n";
		}

		$this->monitorChildren();
	}
}