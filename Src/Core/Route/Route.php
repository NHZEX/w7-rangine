<?php
/**
 * @author donknap
 * @date 18-12-17 下午8:18
 */

namespace W7\Core\Route;


use W7\Core\Route\RouteCollector;
use Illuminate\Support\Str;

class Route {
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	const METHOD_BOTH_GP = 'POST,GET';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';
	const METHOD_HEAD = 'HEAD';
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_ALL = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	private $router;

	/**
	 * 当前路由中间件
	 * @var array
	 */
	private $currentMiddleware = [];

	private $groupBegin = false;

	private $name = '';

	public function __construct() {
		$this->router = new RouteCollector(new \FastRoute\RouteParser\Std(), new \FastRoute\DataGenerator\GroupCountBased());
	}


	public function group($prefix, callable $callback) {
		$this->groupBegin = true;

		$this->router->addGroup($prefix, function (RouteCollector $route) use ($callback, $prefix) {
			$this->name = str_replace('/', '.', trim($this->router->getCurrentGroupPrefix(), '/'));
			$callback($this);
		});

		$this->currentMiddleware = [];
		$this->groupBegin = false;
		return true;
	}


	/**
	 * 注册一个允许所有协议的路由
	 * @param $route
	 * @param $handler
	 */
	public function any($uri, $handler) {
		return $this->add(self::METHOD_ALL, $uri, $handler);
	}

	public function all($uri, $handler) {
		$this->any($uri, $handler);
	}

	/**
	 * 注册一个Post 路由
	 */
	public function post($uri, $handler) {
		$result = $this->add(self::METHOD_POST, $uri, $handler);
		return $result;
	}

	public function get($uri, $handler) {
		$result = $this->add(self::METHOD_GET, $uri, $handler);
		return $result;
	}

	public function put($uri, $handler) {
		$result = $this->add(self::METHOD_PUT, $uri, $handler);
		return $result;
	}

	public function delete($uri, $handler) {
		$result = $this->add(self::METHOD_DELETE, $uri, $handler);
		return $result;
	}

	public function patch($uri, $handler) {
		$result = $this->add(self::METHOD_PATCH, $uri, $handler);
		return $result;
	}

	public function head($uri, $handler) {
		$result = $this->add(self::METHOD_HEAD, $uri, $handler);
		return $result;
	}

	public function options($uri, $handler) {
		$result = $this->add(self::METHOD_OPTIONS, $uri, $handler);
		return $result;
	}

	/**
	 * 注册一个支持多种协议的路由
	 * @param $methods
	 * @param $uri
	 * @param $handler
	 */
	public function add($methods, $uri, $handler, $name = '') {
		$handler = $this->checkHandler($handler);

		if (empty($methods)) {
			$methods = SELF::METHOD_BOTH_GP;
		}
		if (!is_array($methods)) {
			$methods = [$methods];
		}
		//清除掉Method两边的空格
		foreach ($methods as &$value) {
			$value = strtoupper(trim($value));
		}
		unset($value);

		$routeHandler = [
			'handler' => $handler,
			'middleware' => [
				'before' => [],
				'after' => []
			],
			'uri' => $this->router->getCurrentGroupPrefix() . $uri
		];

		if (empty($name)) {
			$name = $this->name;
		}
		//如果是在group内，则以前缀+方法名的规则来命名组内URI
		if (!empty($this->groupBegin) && !($handler instanceof \Closure)) {
			$name = sprintf('%s.%s', $name, $handler[1]);
		}
		if (!empty($name)) {
			$routeHandler['name'] = $name;
		}

		//添加完本次路由后，要清空掉当前Middleware值，以便下次使用
		//如果是在group内，则由group函数来处理清空操作
		if (!empty($this->currentMiddleware)) {
			$routeHandler['middleware']['before'] = array_merge([], $routeHandler['middleware']['before'], $this->checkMiddleware($this->currentMiddleware));
		}

		if (empty($this->groupBegin)) {
			$this->currentMiddleware = [];
		}
		$this->name = '';

		$this->router->addRoute($methods, $uri, $routeHandler);
		return true;
	}

	/**
	 * 注册一个直接跳转路由
	 * @param $uri
	 * @param $destination
	 * @param int $status
	 */
	public function redirect($uri, $destination, $status = 301) {
		throw new \InvalidArgumentException('还未实现');
	}

	/**
	 * 注册一个直接显示的静态页
	 * @param $uri
	 * @param $view
	 * @param array $data
	 */
	public function view($uri, $view, $data = []) {
		throw new \InvalidArgumentException('还未实现');
	}

	public function resource($name, $controller, $options = []) {
		return new ResourceRoute(new ResourceRegister($this), $name, $controller, $options);
	}

	public function apiResource($name, $controller, $options = []) {
		return new ResourceRoute(new ResourceRegister($this), $name, $controller, $options);
	}

	public function middleware($name) {
		if (!is_array($name)) {
			$name = func_get_args();
			$name = [$name];
		}
		foreach ($name as $i => $row) {
			if (!is_array($row)) {
				$row = [$row];
			}
			$this->currentMiddleware[] = $row;
		}

		return $this;
	}

	/**
	 * 指定该路由的名字，用于验权之类的操作
	 * @param $name
	 */
	public function name($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * 获取路由列表
	 * @return array
	 */
	public function getData() {
		return $this->router->getData();
	}


	private function checkHandler($handler) {
		if ($handler instanceof \Closure) {
			return $handler;
		}
		if (is_string($handler)) {
			$handler = explode('@', $handler);
		}
		list($className, $action) = $handler;
		if (empty($action)) {
			$action = 'index';
		}

		if (strpos($className, "\\W7\\App\\Controller\\") === false && strpos($className, "W7\\App\\Controller\\") === false) {
			$className = "\\W7\\App\\Controller\\{$className}";
		}

		$realpath = BASE_PATH . '/app';
		foreach ($path = explode("\\", $className) as $key => $row) {
			if (empty($row) || $row == 'W7' || $row == 'App') {
				continue;
			}

			$realpath .= '/' . $row;
		}

		if (!file_exists($realpath . '.php')) {
			throw new \RuntimeException('Route configuration controller not found. ' . $realpath);
		}

		return [
			$className,
			$action,
		];
	}

	private function checkMiddleware($middleware) {
		if (!is_array($middleware)) {
			$middleware = [$middleware];
		}
		foreach ($middleware as $index => $class) {
			if (!is_array($class)) {
				$class = [$class];
			}
			if (!class_exists($class[0])) {
				$class[0] = "W7\\App\\Middleware\\" . Str::studly($class[0]);
			}
			if (!class_exists($class[0])) {
				unset($middleware[$index]);
			}
			$middleware[$index] = $class;
		}
		return $middleware;
	}
}