<?php

namespace W7\Core\Helper;

/**
 * php帮助类
 * @package Swoft\Helper
 * @author inhere <in.798@qq.com>
 */
class PhpHelper
{
    /**
     * 调用
     *
     * @param mixed $cb   callback函数，多种格式
     * @param array $args 参数
     *
     * @return mixed
     */
    public static function call($cb, array $args = [])
    {
        if (\is_object($cb) || (\is_string($cb) && \function_exists($cb))) {
            $ret = $cb(...$args);
        } elseif (\is_array($cb)) {
            list($obj, $mhd) = $cb;
            $obj = iloader()->singleton($obj);
            $ret = \is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        }
        return $ret;
    }

}
