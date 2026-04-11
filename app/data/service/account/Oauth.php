<?php

namespace app\data\service\account;

use app\data\service\account\oauth\Contract;
use think\admin\Exception;

/**
 * 第三方登录服务工厂
 * @class Oauth
 * @package app\data\service
 */
class Oauth
{
    /**
     * 驱动实例缓存
     * @var array
     */
    protected static $drivers = [];

    /**
     * 获取驱动实例
     * @param string $type 驱动类型
     * @return Contract
     * @throws Exception
     */
    public static function mk(string $type): Contract
    {
        if (isset(self::$drivers[$type])) {
            return self::$drivers[$type];
        }

        $class = "\\app\\data\\service\\oauth\\" . ucfirst(strtolower($type)) . "Service";
        if (class_exists($class)) {
            return self::$drivers[$type] = new $class();
        }

        throw new Exception("不支持的登录方式: {$type}");
    }
}
