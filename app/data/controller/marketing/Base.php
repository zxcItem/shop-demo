<?php

declare(strict_types=1);

namespace app\data\controller\marketing;

use think\admin\Controller;

/**
 * 营销模块基类
 * @class Base
 */
abstract class Base extends Controller
{
    /**
     * 子菜单配置
     * @var array
     */
    protected $menus = [];

    /**
     * 控制器初始化
     */
    protected function initialize()
    {
        parent::initialize();
        // 获取当前完整路径（不含前缀）
        $controller = str_replace('.', '/', strtolower($this->app->request->controller()));
        $action = strtolower($this->app->request->action());
        $currentPath = trim("{$controller}/{$action}", '/');
        
        foreach ($this->menus as &$menu) {
            // 标准化菜单 URL，移除开头的应用名和结尾的 /
            $menuUrl = strtolower(trim($menu['url'], '/'));
            // 移除应用名部分（如 data/）并统一将 . 替换为 /
            $pureUrl = str_replace('.', '/', preg_replace('/^[^\/]+\//', '', $menuUrl));
            
            // 匹配逻辑：如果是完整路径匹配，或者是控制器路径匹配且动作为 index
            $menu['active'] = ($pureUrl === $currentPath) || ($pureUrl === $controller && $action === 'index');
        }
        $this->assign('menus', $this->menus);
    }
}
