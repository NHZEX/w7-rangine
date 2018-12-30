<?php
/**
 * @author donknap
 * @date 18-10-23 上午11:48
 */

namespace W7\Core\Database\Pool;


use Illuminate\Database\Connectors\MySqlConnector;
use W7\Core\Pool\CoPoolAbstract;

class Pool extends CoPoolAbstract {
	/**
	 * 用于创建连接对象
	 * @var MySqlConnector
	 */
	private $creator;

	public function setCreator($creator) {
		$this->creator = $creator;
	}

	public function createConnection() {
		if (empty($this->creator)) {
			throw new \RuntimeException('Invalid db creator');
		}
		$connection = $this->creator->connect($this->config);
		$connection->poolName = sprintf('%s:%s', $this->config['driver'], $this->poolName);
		return $connection;
	}
}