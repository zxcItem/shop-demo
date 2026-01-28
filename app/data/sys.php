<?php

declare (strict_types=1);


use think\Console;
use think\admin\Library;

if (Library::$sapp->request->isCli()) {
    // 动态注册操作指令
    Console::starting(function (Console $console) {

    });
}else{



}

if (!function_exists('show_gspec')) {
    /**
     * 商品规格过滤显示
     * @param string $spec 原规格内容
     * @return string
     */
    function show_gspec(string $spec): string
    {
        $specs = [];
        foreach (explode(';;', $spec) as $sp) {
            $specs[] = explode('::', $sp)[1];
        }
        return join(' ', $specs);
    }
}
if (!function_exists('formatdate')) {
    /**
     * 日期格式过滤
     * @param string|null $value
     * @return string|null
     */
    function formatdate(?string $value): ?string
    {
        return is_string($value) ? str_replace(['年', '月', '日'], ['-', '-', ''], $value) : $value;
    }
}

if (!function_exists('processdate')) {
    /**
     * 日期格式处理
     * @param string|null $value
     * @return string
     */
    function processdate(?string $value): string
    {
        if (empty($value)) return '';
        // 处理中文日期格式 "2025年12月31日 13:50:37" -> "2025-12-31 13:50:37"
        $value = str_replace(['年', '月', '日'], ['-', '-', ''], $value);
        $timestamp = strtotime($value);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');
        if ($timestamp >= $today) {
            return '今天 ' . date('H:i', $timestamp);
        } elseif ($timestamp >= $yesterday) {
            return '昨天 ' . date('H:i', $timestamp);
        } else {
            return date('m-d H:i', $timestamp);
        }
    }
}
if (!function_exists('formatmonthday')) {
    /**
     * 日期格式处理
     * @param string|null $value
     * @return string
     */
    function formatmonthday(?string $value): string
    {
        if (empty($value)) return '';
        $value = str_replace(['年', '月', '日'], ['-', '-', ''], $value);
        return date('Y-m-d',strtotime($value));
    }
}