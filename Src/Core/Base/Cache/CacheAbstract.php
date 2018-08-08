<?php
/**
 * 缓存接口类
 * @author donknap
 * @date 18-7-19 上午10:04
 */
namespace W7\Core\Base\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * @method string|bool get($key, $default = null)
 * @method bool delete($key)
 * @method bool clear()
 * @method array getMultiple($keys, $default = null)
 * @method bool setMultiple($values, $ttl = null)
 * @method bool deleteMultiple($keys)
 * @method int has($key)
 */
abstract class CacheAbstract implements CacheInterface
{
	protected static $driverType;

	/**
	 * @var array
	 */
	private $drivers = [];

	/**
	 * TODO add serializer mechanism
	 * @var null|string
	 */
	private $serializer = null;
	abstract public function getDriver(string $driverType = null);
	/**
	 * @return array
	 */
	protected function getDrivers(): array
	{
		return array_merge(static::$driverType, $this->defaultDrivers());
	}

	/**
	 * @return array
	 */
	protected function defaultDrivers()
	{
		return [
			'memory' => '',
		];
	}
	abstract public function set($key, $value, $ttl=null);
}
