<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-25
 * Time: 上午10:43
 */

namespace W7\Core\Helper;


class RouteData
{
    public static function middlerWareData()
    {
        $middlerwares = [];
        $configData = iconfig()->getUserConfig("route");
        foreach ($configData as $controller => $route)
        {
            if (isset($route['common']) && empty($route['common']))
            {
                $middlerwares['controller_midllerware'][$controller] = $route['common'];
            }
            $middlerwares['method_middlerware'] = static::methodMiddlerWare($route, $controller);

        }
        return $middlerwares;
    }

    protected static function methodMiddlerWare($routeData, $controller)
    {
        $methodMiddlewares = [];
        foreach ($routeData as $method=>$data)
        {
            if (isset($data['middleware']) && !empty($data['middleware']))
            {
                $methodMiddlewares[$controller . '_' . $method] = $data['middleware'];
            }
        }
        return $methodMiddlewares;
    }

    public static function routeData()
    {
        $routes = [];
        $configData = iconfig()->getUserConfig("route");
        foreach ($configData as $controller => $route)
        {
            $routes = static::methodRouteData($route, $controller, $routes);
        }
        return $routes;
    }

    protected static function methodRouteData($routeData, $controller, $routes)
    {
        foreach ($routeData as $method=>$data)
        {
            if (isset($data['method']) && !empty($data['method']))
            {
                if (!strstr($data['method'], ',')) {
                    $routes[$data['method']][$controller. '-' . $method] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . $data['query'];
                }else
                {
                    $httpMethod = [];
                    $httpMethod = explode(',', $data['method']);
                    $routes[$httpMethod[0]][$controller. '-' . $method] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . $data['query'];
                    $routes[$httpMethod[1]][$controller. '-' . $method] = DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $method . DIRECTORY_SEPARATOR . $data['query'];
                }
            }
        }
        return $routes;
    }
}