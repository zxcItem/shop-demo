<?php

use think\facade\Route;

// 定义单个路由规则，例如 /json.html 路由到 index 应用 tools 控制器的 json 方法
Route::rule('tools', 'tools/index')->ext('html');
Route::rule('json', 'tools/json')->ext('html');
Route::rule('date', 'tools/date')->ext('html');
Route::rule('base64', 'tools/base64')->ext('html');
Route::rule('url', 'tools/url')->ext('html');
Route::rule('number', 'tools/number')->ext('html');
Route::rule('unicode', 'tools/unicode')->ext('html');
Route::rule('md5', 'tools/md5')->ext('html');
Route::rule('random', 'tools/random')->ext('html');
Route::rule('qrcode', 'tools/qrcode')->ext('html');
Route::rule('html_entity', 'tools/html_entity')->ext('html');
Route::rule('regex', 'tools/regex')->ext('html');
Route::rule('color', 'tools/color')->ext('html');
Route::rule('diff', 'tools/diff')->ext('html');
Route::rule('cron', 'tools/cron')->ext('html');
Route::rule('sql', 'tools/sql')->ext('html');
Route::rule('xml', 'tools/xml')->ext('html');
Route::rule('ip', 'tools/ip')->ext('html');
Route::rule('ua', 'tools/ua')->ext('html');
Route::rule('http', 'tools/http')->ext('html');
Route::rule('image_base64', 'tools/image_base64')->ext('html');
Route::rule('markdown', 'tools/markdown')->ext('html');
Route::rule('css', 'tools/css')->ext('html');
