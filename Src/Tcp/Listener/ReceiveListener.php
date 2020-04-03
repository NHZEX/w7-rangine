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

namespace W7\Tcp\Listener;

use W7\App;
use Swoole\Coroutine;
use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Tcp\Server\Dispatcher as RequestDispatcher;

class ReceiveListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId, $data) = $params;

		$this->dispatch($server, $reactorId, $fd, $data);
	}

	private function dispatch(Server $server, $reactorId, $fd, $data) {
		$context = App::getApp()->getContext();
		$context->setContextDataByKey('fd', $fd);
		$context->setContextDataByKey('reactorid', $reactorId);
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$collector = icontainer()->get('tcp-client')[$fd] ?? [];

		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = $collector[0];
		$psr7Request = $psr7Request->loadFromTcpData($data);

		/**
		 * @var Psr7Response $psr7Response
		 */
		$psr7Response = $collector[1];

		App::getApp()->getContext()->setResponse($psr7Response);
		App::getApp()->getContext()->setRequest($psr7Request);

		ievent(ServerEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response]);

		/**
		 * @var RequestDispatcher $dispatcher
		 */
		$dispatcher = \icontainer()->singleton(RequestDispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$psr7Response->send();

		ievent(ServerEvent::ON_USER_AFTER_REQUEST);
		icontext()->destroy();
	}
}
