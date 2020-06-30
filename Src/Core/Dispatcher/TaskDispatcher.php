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

namespace W7\Core\Dispatcher;

use W7\App;
use W7\Core\Exception\TaskException;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;

/**
 * 派发任务的时候，需要先注册任务，然后在OnTask事件中具体调用
 * Class TaskDispatcher
 * @package W7\Core\Helper\Dispather
 */
class TaskDispatcher extends DispatcherAbstract {
	/**
	 * 注册一个异步任务
	 * @param string $taskName
	 * @param string $methodName
	 * @param array $params
	 * @param string $type
	 * @param int $timeout
	 * @return false|int
	 * @throws TaskException
	 */
	public function register(...$params) {
		/**
		 * @var TaskMessage $message
		 */
		list($message) = $params;

		if (!($message instanceof TaskMessage)) {
			throw new \RuntimeException('Invalid task message');
		}

		if (!isWorkerStatus()) {
			throw new TaskException('Please deliver task by http!');
		}

		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		return App::$server->getServer()->task($message->pack());
	}

	/**
	 * 注册一个协程任务
	 * @param TaskMessage $message
	 * @return mixed
	 * @throws TaskException
	 */
	public function registerCo(TaskMessage $message) {
		if (!isWorkerStatus()) {
			throw new TaskException('Please deliver task by http!');
		}

		if (!class_exists($message->task)) {
			throw new TaskException('Task ' . $message->task . ' not found');
		}

		return App::$server->getServer()->taskCo($message->pack());
	}

	/**
	 * 在OnTask事件中执行具体任务
	 * @param mixed ...$params
	 * @return mixed|void
	 * @throws \Throwable
	 */
	public function dispatch(...$params) {
		list($server, $taskId, $workId, $data) = $params;

		$message = Message::unpack($data);

		Context::setContextDataByKey('workid', $workId);
		Context::setContextDataByKey('coid', $taskId);

		if (!class_exists($message->task)) {
			$message->task = 'W7\\App\\Task\\'. ucfirst($message->task);
		}

		if (!class_exists($message->task)) {
			throw new \RuntimeException('task ' . $message->task . ' not exists');
		}

		$task = Container::get($message->task);
		if (method_exists($task, 'finish')) {
			$message->hasFinishCallback = true;
		}

		try {
			$message->result = call_user_func_array([$task, $message->method], $params);
		} catch (\Throwable $e) {
			throw $e;
		}
		//return 时将消息传递给 onFinish 事件
		//onFinish 回调还需要处理一下用户定义的任务回调方法
		return $message;
	}
}
