<?php

declare(strict_types=1);

namespace app\data\controller\marketing\integral;


use app\data\controller\marketing\Base;

/**
 * 积分商城管理
 * @class Integral
 */
class Integral extends Base
{
    protected $menus = [
        ['title' => '商城设置', 'url' => 'data/marketing.integral.config/index'],
        ['title' => '商品管理', 'url' => 'data/marketing.integral.integral/index'],
        ['title' => '兑换记录', 'url' => 'data/marketing.integral.order/index'],
    ];

    /**
     * 商品管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '商品管理';
        $this->fetch();
    }

    /**
     * 添加商品
     * @auth true
     */
    public function add()
    {
        $this->title = '添加商品';
        $this->_form('data_shop_marketing_integral', 'form');
    }

    /**
     * 编辑商品
     * @auth true
     */
    public function edit()
    {
        $this->title = '编辑商品';
        $this->_form('data_shop_marketing_integral', 'form');
    }

    /**
     * 删除商品
     * @auth true
     */
    public function remove()
    {
        $this->_delete('data_shop_marketing_integral');
    }
}
