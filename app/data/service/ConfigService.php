<?php

declare (strict_types=1);

namespace app\data\service;

use think\admin\Exception;

/**
 * 商城配置服务
 * @class ConfigService
 * @package app\data\service
 */
abstract class ConfigService
{
    /**
     * 配置缓存名
     * @var string
     */
    private static $skey = 'data.config';

    /**
     * 页面类型配置
     * @var string[]
     */
    public static $pageTypes = [
        [
            'name' => 'user_agreement',
            'title' => '用户协议',
            'temp'  => 'content'
        ],
        [
            'name' => 'merchant_discount',
            'title' => '商户折扣',
            'temp'  => 'content'
        ],
        [
            'name' => 'index_slider_page',
            'title' => '首页默认轮播',
            'temp'  => 'slider'
        ]
    ];

    /**
     * 类型配置获取
     * @param string $name
     * @return mixed
     */
    public static function pageTypes(string $name)
    {
        return array_column(self::$pageTypes,'title','name')[$name];
    }

    /**
     * 读取配置参数
     * @param string|null $name
     * @param $default
     * @return array|mixed|null
     * @throws Exception
     */
    public static function get(?string $name = null, $default = null)
    {
        $syscfg = sysvar(self::$skey) ?: sysvar(self::$skey, sysdata(self::$skey));
        return is_null($name) ? $syscfg : ($syscfg[$name] ?? $default);
    }

    /**
     * 保存商城配置参数
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public static function set(array $data)
    {
        return sysdata(self::$skey, $data);
    }

    /**
     * 设置页面数据
     * @param string $code 页面编码
     * @param array $data 页面内容
     * @return mixed
     * @throws \think\admin\Exception
     */
    public static function setPage(string $code, array $data)
    {
        return sysdata("data.page.{$code}", $data);
    }

    /**
     * 获取页面内容
     * @param string $code
     * @return array
     * @throws \think\admin\Exception
     */
    public static function getPage(string $code): array
    {
        return sysdata("data.page.{$code}");
    }
}