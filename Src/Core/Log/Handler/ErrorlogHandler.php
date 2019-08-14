<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:26
 */

namespace W7\Core\Log\Handler;


use Monolog\Handler\HandlerInterface as MonologInterface;

class ErrorlogHandler extends \Monolog\Handler\ErrorLogHandler  implements HandlerInterface {
	public static function getHandler($config): MonologInterface {
		return new static();
	}
}