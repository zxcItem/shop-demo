<?php

use think\facade\Route;

// 首页路由
Route::get('/', 'index/index');

// 工具路由，支持 /json.html, /base64.html 等
Route::get(':name', 'index.tools/:name')->pattern(['name' => '\w+'])->ext('html');

// 兼容旧的路由方式
Route::get('tools/:name', 'index.tools/:name');
