<?php
/**
 * 保存中间件数据
 * @author alex
 * @date 18-7-24 下午5:31
 */

namespace W7\Core\Helper;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Table;
use w7\Http\Message\Server\Request;
use W7\Http\Middleware\RequestMiddleware;

class Middleware
{


    const MIDDLEWARE_MEMORY_TABLE_NAME = 'middleware_memory';

    const MEMORY_CACHE_TYPE = 2;

    protected $lastMiddleware;
    //1191014510910510010010810111997114101 转化32进制去掉0000000000000
    const MEMORY_CACHE_KEY = 'slgop41mg0c';


    public $cacheType = self::MEMORY_CACHE_TYPE;


    /**
     * @param string $middlerware
     * @param array $dispather
     * @return array
     */
    public function setLastMiddleware(string $middlerware, array $handler)
    {
        $this->lastMiddleware = $middlerware;
        $middlewares = $this->findMiddlewaresByDispather($handler);
        array_unshift($middlewares, $this->lastMiddleware);
        return $middlewares;
    }

    protected function findMiddlewaresByDispather(array $handler)
    {
        $result = [];
        $middlewares = Context::getShareContextDataByKey(static::MIDDLEWARE_MEMORY_TABLE_NAME);
        $controllerMiddlerwares = !empty($middlewares[$handler['middlerware_key']])?$middlewares[$handler['middlerware_key']]:[];
        foreach ($controllerMiddlerwares as $method => $middlerware)
        {
            if (strstr($method, $handler['method'])|| $method == "default") {
                $result = array_merge($result,$controllerMiddlerwares[$method]);
            }
        }
        return $result;
    }
    /**
     * @param int $cacheType
     * @param string|null $filePath
     * @param Table|null $tableObj
     */
    public static function getMiddlewares(array $middlewares)
    {

        $systemMiddlerwares = iconfig()->getUserConfig("define");
        $beforeMiddlerwares = !empty($systemMiddlerwares['middlerware']['befor_middlerware'])?$systemMiddlerwares['middlerware']['befor_middlerware']:[];
        $afterMiddlerwares  = !empty($systemMiddlerwares['middlerware']['after_middlerware'])?$systemMiddlerwares['middlerware']['after_middlerware']:[];
        $middlewares = array_merge($beforeMiddlerwares, $middlewares, $afterMiddlerwares);
        return $middlewares;
    }

    protected  static function formatData(array $commonMiddlewares, array $methodMiddlewares)
    {
        $middlewares = [];
        foreach($commonMiddlewares as $controller=>$middleware)
        {
            if (empty($middleware)){
                continue;
            }
            $middlewares[$controller]['default'] = $middleware;
        }
        foreach($methodMiddlewares as $method=>$middleware)
        {
            if (empty($middleware)){
                continue;
            }
            $middlewares[$controller][$method] = $middleware;
        }
        return $middlewares;
    }

    /**
     * @param array $middlewares
     * @param int $cacheType
     * @param string $filePath
     * @return bool|Table
     */
    public static function insertMiddlewareCached()
    {
        $dataHepler  = new RouteData();
        $middlewares = $dataHepler->middlerWareData();
        $middlewares = static::formatData($middlewares['controller_midllerware'], $middlewares['method_middlerware']);
        $middlewares = static::getMiddlewares($middlewares);
        Context::setShareContextDataByKey(static::MIDDLEWARE_MEMORY_TABLE_NAME, $middlewares);
    }

}