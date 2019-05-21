<?php
/**
 * 开启服务之前，先构造中间件数据及路由数据
 * @author donknap
 * @date 18-7-25 下午4:51
 */

namespace W7\Http\Listener;

use W7\Core\Container\Context;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Middleware\MiddlewareMapping;
use W7\Core\Route\RouteMapping;
use FastRoute\Dispatcher\GroupCountBased;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		//注册路由的时候会调用中间件生成，所以要先生成路由再中间件
		iloader()->set(Context::ROUTE_KEY, $this->getRoute());
		iloader()->set(Context::MIDDLEWARE_KEY, $this->getLastMiddleware());
		return true;
	}

	/**
	 * @return GroupCountBased
	 */
	private function getRoute() {
		$routeInfo = iloader()->get(RouteMapping::class)->getMapping();
		return new GroupCountBased($routeInfo);
	}

	private function getLastMiddleware() {
		/**
		 * @var MiddlewareMapping $middlerwareObj
		 */
		$middlerwareObj = iloader()->get(MiddlewareMapping::class);
		return $middlerwareObj->getLastMiddle();
	}
}
