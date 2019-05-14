<?php
/**
 * @author donknap
 * @date 18-8-24 下午4:33
 */
namespace W7\Core\Exception;

use W7\App;

class HttpException extends \LogicException {
	public function render() {
		return App::getApp()->getContext()->getResponse()->json(['error' => $this->getMessage()], $this->getCode());
	}
}