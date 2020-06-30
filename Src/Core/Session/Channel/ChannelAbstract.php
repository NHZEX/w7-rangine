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

namespace W7\Core\Session\Channel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class ChannelAbstract {
	protected $config;
	protected static $sessionName;
	protected $sessionId;
	protected $expires;
	/**
	 * @var ServerRequestInterface
	 */
	protected $request;

	public function __construct($config, ServerRequestInterface $request) {
		$this->config = $config;
		$this->request = $request;
	}

	protected function getSessionName() {
		if (!static::$sessionName) {
			static::$sessionName = $this->config['name'] ?? session_name();
		}
		return static::$sessionName;
	}

	protected function generateId() {
		$sessionId = session_id();
		return !empty($sessionId) ? $sessionId : \session_create_id();
	}

	final public function getSessionId() {
		if (!$this->sessionId) {
			$this->sessionId = $this->generateId();
		}

		return $this->sessionId;
	}

	final public function setSessionId($sessionId) {
		$this->sessionId = $sessionId;
	}

	abstract public function replenishResponse(ResponseInterface $response) : ResponseInterface;
}
