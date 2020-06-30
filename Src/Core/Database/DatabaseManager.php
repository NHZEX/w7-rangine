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

namespace W7\Core\Database;

use W7\Core\Facades\Context;

class DatabaseManager extends \Illuminate\Database\DatabaseManager {
	public function connection($name = null) {
		list($database, $type) = $this->parseConnectionName($name);
		$name = $name ?: $database;

		//这里不同于父函数，要做一个单例返回
		//外部还会接连接池，所以此处直接生成对象
		$connection = Context::getContextDataByKey('db-transaction');
		if ($connection) {
			$this->connections[$name] = $connection;
		} else {
			$this->connections[$name] = $this->configure(
				$this->makeConnection($database),
				$type
			);
		}

		return $this->connections[$name];
	}

	public function beginTransaction($name = null) {
		return $this->connection($name)->beginTransaction();
	}
}
