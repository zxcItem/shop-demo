<?php

namespace app\data\service;

use app\data\service\vod\Contract;
use think\admin\Exception;

/**
 * 视频点播服务工厂
 * @class Vod
 * @package app\data\service
 */
class Vod
{
    /**
     * 阿里云点播
     */
    public const ALIYUN = 'aliyun';

    /**
     * 腾讯云点播
     */
    public const TENCENT = 'tencent';

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

        $class = "\\app\\data\\service\\vod\\" . ucfirst(strtolower($type)) . "Service";
        if (class_exists($class)) {
            return self::$drivers[$type] = new $class();
        }

        throw new Exception("不支持的点播方式: {$type}");
    }
}
