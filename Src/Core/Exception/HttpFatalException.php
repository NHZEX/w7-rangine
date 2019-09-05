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

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use Whoops\Exception\Inspector;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class HttpFatalException extends FatalExceptionAbstract {
	protected function development(): ResponseInterface {
		if ((ENV & BACKTRACE) !== BACKTRACE) {
			$content = 'message: ' . $this->getMessage() . '<br/>file: ' . $this->getPrevious()->getFile() . '<br/>line: ' . $this->getPrevious()->getLine();
		} else {
			ob_start();
			$render = new PrettyPageHandler();
			$render->handleUnconditionally(true);
			$render->setException($this->getPrevious());
			$render->setInspector(new Inspector($this->getPrevious()));
			$render->setRun(new Run());
			$render->handle();
			$content = ob_get_clean();
		}

		return $this->response->withStatus(500)->html($content);
	}

	protected function release(): ResponseInterface {
		return $this->response->withStatus(500)->withData(['error' => '系统内部错误']);
	}
}