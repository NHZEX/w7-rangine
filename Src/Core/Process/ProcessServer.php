<?php

namespace W7\Core\Process;


use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\Pool\PoolServerAbstract;

class ProcessServer extends PoolServerAbstract {
	const DEFAULT_PID_FILE = '/tmp/swoole_user_process.pid';
	private $userProcess;


	public function __construct() {
		$this->config = iconfig()->getUserConfig('process');
		$this->config['setting']['pid_file'] = empty($this->config['setting']['pid_file']) ? self::DEFAULT_PID_FILE : $this->config['setting']['pid_file'];
		$this->poolConfig = $this->config['setting'];
	}

	public function getUserProcess() {
		if (!$this->userProcess) {
			$process = iconfig()->getUserConfig('process')['process'];
			foreach ($process as $key => $item) {
				if ($process[$key]['enable']) {
					$this->userProcess[$key] = $item;
				}
			}
		}

		return $this->userProcess;
	}

	public function getType() {
		return parent::TYPE_PROCESS;
	}

	public function start() {
		$userProcess = $this->getUserProcess();
		if (!$userProcess) {
			return false;
		}

		foreach ($userProcess as $name => $process) {
			if ($name === 'reload' && $this->processPool instanceof IndependentPool) {
				continue;
			}
			$this->processPool->registerProcess($name, $process['class'], $process['number']);
		}
		$this->processPool->start();
	}
}