<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Process\Pool;

use Swoole\Process;
use Swoole\Process\Pool as PoolManager;
use W7\App;

/**
 * The process pool is managed by a separate Process Manager
 * Class IndependentPool
 * @package W7\Core\Process\Pool
 */
class IndependentPool extends PoolAbstract {
	private $ipcType = SWOOLE_IPC_UNIXSOCK;
	/**
	 * @var Process\Pool
	 */
	protected $swooleProcessPool;
	private $pidFile;
	private $daemon;
	private $events = [];

	protected function init() {
		$this->pidFile = $this->config['pid_file'];
		$this->daemon = $this->config['daemonize'] ?? false;
	}

	private function setDaemon() {
		if ($this->daemon) {
			Process::daemon(true, false);
		}
	}

	public function start() {
		if ($this->processFactory->count() == 0) {
			return false;
		}

		$this->setDaemon();

		$this->swooleProcessPool = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey, true);
		foreach ($this->events as $event => $handler) {
			try {
				$this->swooleProcessPool->on($event, $handler);
			} catch (\Throwable $e) {
			}
		}

		file_put_contents($this->pidFile, getmypid());

		isetProcessTitle(App::$server->getPname() . 'process manager');
		$this->swooleProcessPool->start();
	}

	public function on($event, \Closure $handler) {
		$this->events[$event] = $handler;
	}

	public function getProcess($id) {
		$process = parent::getProcess($id);
		if (!$process->getProcess() && $this->swooleProcessPool) {
			$swooleProcess = $this->swooleProcessPool->getProcess($id);
			$swooleProcess && $process->setProcess($swooleProcess);
		}

		return $process;
	}
}
