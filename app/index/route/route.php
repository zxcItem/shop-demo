<?php

use think\facade\Route;

// 定义单个路由规则，例如 /json.html 路由到 index 应用 tools 控制器的 json 方法
Route::rule('json', 'index/tools/json')->ext('html');
Route::rule('date', 'index/tools/date')->ext('html');
Route::rule('base64', 'index/tools/base64')->ext('html');
Route::rule('url', 'index/tools/url')->ext('html');
Route::rule('number', 'index/tools/number')->ext('html');
Route::rule('unicode', 'index/tools/unicode')->ext('html');
Route::rule('md5', 'index/tools/md5')->ext('html');
Route::rule('random', 'index/tools/random')->ext('html');
Route::rule('qrcode', 'index/tools/qrcode')->ext('html');
Route::rule('html_entity', 'index/tools/html_entity')->ext('html');
Route::rule('regex', 'index/tools/regex')->ext('html');
Route::rule('color', 'index/tools/color')->ext('html');
Route::rule('diff', 'index/tools/diff')->ext('html');
Route::rule('cron', 'index/tools/cron')->ext('html');
Route::rule('sql', 'index/tools/sql')->ext('html');
Route::rule('xml', 'index/tools/xml')->ext('html');
Route::rule('ip', 'index/tools/ip')->ext('html');
Route::rule('ua', 'index/tools/ua')->ext('html');
Route::rule('http', 'index/tools/http')->ext('html');
Route::rule('image_base64', 'index/tools/image_base64')->ext('html');
Route::rule('markdown', 'index/tools/markdown')->ext('html');
Route::rule('css', 'index/tools/css')->ext('html');
