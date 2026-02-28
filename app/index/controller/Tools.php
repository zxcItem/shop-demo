<?php

namespace app\index\controller;

use think\admin\Controller;

/**
 * 工具箱控制器
 * @class Tools
 * @package app\index\controller
 */
class Tools extends Controller
{
    /**
     * 工具箱首页
     * @return void
     */
    public function index()
    {
        $this->title = '在线工具箱';
        $this->fetch();
    }

    /**
     * JSON 格式化工具
     * @return void
     */
    public function json()
    {
        $this->title = 'JSON 格式化工具';
        $this->fetch();
    }

    /**
     * 日期时间工具
     * @return void
     */
    public function date()
    {
        $this->title = '日期时间转换工具';
        $this->fetch();
    }

    /**
     * Base64 编解码
     */
    public function base64()
    {
        $this->title = 'Base64 编解码';
        $this->fetch();
    }

    /**
     * URL 编解码
     */
    public function url()
    {
        $this->title = 'URL 编解码';
        $this->fetch();
    }

    /**
     * 进制转换
     */
    public function number()
    {
        $this->title = '进制转换';
        $this->fetch();
    }

    /**
     * Unicode 转换
     */
    public function unicode()
    {
        $this->title = 'Unicode 转换';
        $this->fetch();
    }

    /**
     * MD5/SHA 哈希
     */
    public function md5()
    {
        $this->title = 'MD5/SHA 哈希';
        $this->fetch();
    }

    /**
     * 随机密码生成
     */
    public function random()
    {
        $this->title = '随机密码生成';
        $this->fetch();
    }

    /**
     * 二维码生成
     */
    public function qrcode()
    {
        $this->title = '二维码生成';
        $this->fetch();
    }

    /**
     * HTML 实体编码
     */
    public function html_entity()
    {
        $this->title = 'HTML 实体编码';
        $this->fetch();
    }

    /**
     * 正则测试
     */
    public function regex()
    {
        $this->title = '正则测试';
        $this->fetch();
    }

    /**
     * 颜色转换
     */
    public function color()
    {
        $this->title = '颜色转换';
        $this->fetch();
    }

    /**
     * 文本差异对比
     */
    public function diff()
    {
        $this->title = '文本差异对比';
        $this->fetch();
    }

    /**
     * Cron 表达式
     */
    public function cron()
    {
        $this->title = 'Cron 表达式';
        $this->fetch();
    }

    /**
     * SQL 格式化
     */
    public function sql()
    {
        $this->title = 'SQL 格式化';
        $this->fetch();
    }

    /**
     * XML/JSON 互转
     */
    public function xml()
    {
        $this->title = 'XML/JSON 互转';
        $this->fetch();
    }

    /**
     * CSS 格式化
     */
    public function css()
    {
        $this->title = 'CSS 格式化';
        $this->fetch();
    }

    /**
     * Markdown 预览
     */
    public function markdown()
    {
        $this->title = 'Markdown 预览';
        $this->fetch();
    }

    /**
     * IP 地址查询
     */
    public function ip()
    {
        $this->title = 'IP 地址查询';
        $this->fetch();
    }

    /**
     * User-Agent 分析
     */
    public function ua()
    {
        $this->title = 'User-Agent 分析';
        $this->fetch();
    }

    /**
     * HTTP 接口测试
     */
    public function http()
    {
        $this->title = 'HTTP 接口测试';
        $this->fetch();
    }

    /**
     * 图片 Base64
     */
    public function image_base64()
    {
        $this->title = '图片 Base64';
        $this->fetch();
    }
}
