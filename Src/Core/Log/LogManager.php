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

namespace W7\Core\Log;

use Monolog\Logger as MonoLogger;
use Psr\Log\LoggerInterface;
use W7\Core\Log\Handler\HandlerAbstract;

/**
 * Class LogManager
 * @package W7\Core\Log
 *
 * @method void emergency(string $message, array $context = [])
 * @method void alert(string $message, array $context = [])
 * @method void critical(string $message, array $context = [])
 * @method void error(string $message, array $context = [])
 * @method void warning(string $message, array $context = [])
 * @method void notice(string $message, array $context = [])
 * @method void info(string $message, array $context = [])
 * @method void debug(string $message, array $context = [])
 * @method void log($level, string $message, array $context = [])
 */
class LogManager {
	protected $channelsConfig;
	protected $defaultChannel;
	protected $loggers = [];

	public function __construct($channelsConfig = [], $defaultChannel = 'stack') {
		$this->channelsConfig = $channelsConfig;
		$this->defaultChannel = $defaultChannel;
	}

	public function setDefaultChannel(string $channel) {
		$this->defaultChannel = $channel;
	}

	/**
	 * 需调整
	 * @param string $channel
	 * @return LoggerInterface
	 */
	public function channel($channel = 'stack') : LoggerInterface {
		return $this->getLogger($channel);
	}

	protected function getLogger($channel) : LoggerInterface {
		if (empty($this->loggers[$channel]) && !empty($this->channelsConfig[$channel])) {
			$this->registerLogger($channel, $this->channelsConfig[$channel]);
		}
		if (empty($this->loggers[$channel])) {
			$channel = $this->defaultChannel;
		}

		if (!empty($this->loggers[$channel]) && $this->loggers[$channel] instanceof MonoLogger) {
			return $this->loggers[$channel];
		}

		throw new \RuntimeException('logger channel ' . $channel . ' not support');
	}

	public function registerLogger($channel, array $config) {
		$logger = new Logger($channel, [], []);
		$logger->bufferLimit = $config['buffer_limit'] ?? 1;

		$handler = $config['driver'];
		$handlers = [];
		if ($handler != 'stack') {
			/**
			 * @var HandlerAbstract $handler
			 */
			$handlers[] = new LogBuffer($handler::getHandler($config), $logger->bufferLimit, $config['level'], true, true);
		} else {
			$config['channel'] = (array)$config['channel'];
			foreach ($config['channel'] as $childChannel) {
				/**
				 * @var Logger $channelLogger
				 */
				$channelLogger = $this->getLogger($childChannel);
				$handlers = array_merge($handlers, $channelLogger->getHandlers());
			}
		}
		foreach ($handlers as $handler) {
			$logger->pushHandler($handler);
		}

		$config['processor'] = (array)(empty($config['processor']) ? [] : $config['processor']);
		foreach ($config['processor'] as $processor) {
			$logger->pushProcessor(new $processor);
		}

		$this->loggers[$channel] = $logger;

		return $logger;
	}

	public function __call($name, $arguments) {
		return $this->channel()->$name(...$arguments);
	}
}
