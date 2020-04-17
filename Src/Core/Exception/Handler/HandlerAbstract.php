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

namespace W7\Core\Exception\Handler;

use W7\Core\Exception\FatalExceptionAbstract;
use W7\Core\Exception\ResponseExceptionAbstract;
use W7\Http\Message\Server\Response;

abstract class HandlerAbstract {
	protected $serverType;
	protected $response;
	/**
	 * @var HandlerAbstract
	 */
	protected $beforeHandler;

	public function setServerType($serverType): void {
		$this->serverType = $serverType;
	}

	protected function getServerType() {
		return $this->serverType;
	}

	public function setBeforeHandler(HandlerAbstract $handler) {
		$this->beforeHandler = $handler;
	}

	public function setResponse(Response $response): void {
		$this->response = $response;
	}

	/**
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	public function report(\Throwable $throwable) {
		if ($throwable instanceof ResponseExceptionAbstract) {
			if (!$throwable->isLoggable) {
				return true;
			}
		}
		if ($throwable instanceof FatalExceptionAbstract) {
			$throwable = $throwable->getPrevious();
		}

		$errorMessage = sprintf(
			'Uncaught Exception %s: "%s" at %s line %s',
			get_class($throwable),
			$throwable->getMessage(),
			$throwable->getFile(),
			$throwable->getLine()
		);

		$context = [];
		if ((ENV & BACKTRACE) === BACKTRACE) {
			$context = array('exception' => $throwable);
		}

		ilogger()->debug($errorMessage, $context);
	}

	/**
	 * 此函数用于接管代码中抛出的异常，根据情况来做处理
	 * 业务层也可替换此类
	 * @param \Throwable $e
	 * @return Response
	 */
	public function handle(\Throwable $e) : Response {
		// ResponseExceptionAbstract 为特殊的异常，此异常不管何时都将反馈给客户端
		if ($e instanceof ResponseExceptionAbstract) {
			return $this->getResponse()->withStatus($e->getCode() ?? '500')->withContent($e->getMessage());
		}

		if ((ENV & DEBUG) === DEBUG) {
			return $this->handleDevelopment($e);
		} else {
			return $this->handleRelease($e);
		}
	}

	/**
	 * 用于处理正式环境的错误返回
	 * @param \Throwable $e
	 * @return Response
	 */
	protected function handleRelease(\Throwable $e) : Response {
		if ($this->beforeHandler) {
			return $this->beforeHandler->handleRelease($e);
		}
		return $this->getResponse()->withStatus(500)->withContent(\json_encode(['error' => '系统内部错误']));
	}

	/**
	 * 用于处理开发环境的错误返回
	 * 处理异常时将按照服务各自定义的FatalException异常来再次包装错误信息
	 * @param \Throwable $e
	 * @return Response
	 */
	protected function handleDevelopment(\Throwable $e) : Response {
		if ($this->beforeHandler) {
			return $this->beforeHandler->handleDevelopment($e);
		}
		return $this->getResponse()->withStatus(500)->withContent($e->getMessage());
	}
}
