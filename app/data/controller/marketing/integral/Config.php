<?php

declare(strict_types=1);

namespace app\data\controller\marketing\integral;


use app\data\controller\marketing\Base;

/**
 * 积分商城设置
 * @class Config
 */
class Config extends Base
{
    protected $menus = [
        ['title' => '商城设置', 'url' => 'data/marketing.integral.config/index'],
        ['title' => '商品管理', 'url' => 'data/marketing.integral.integral/index'],
        ['title' => '兑换记录', 'url' => 'data/marketing.integral.order/index'],
    ];

    /**
     * 商城设置
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->skey = 'shop_marketing_integral';
        if ($this->request->isGet()) {
            $this->title = '商城设置';
            $data = sysdata($this->skey);
            $this->data = array_merge([
                'status' => '1',
                'name'   => '积分商城',
                'banner' => '',
                'remark' => '积分换好物，惊喜享不停！'
            ], is_array($data) ? $data : []);
            $this->fetch();
        } else {
            $post = $this->request->post();
            if (sysdata($this->skey, $post)) {
                $this->success('保存设置成功！');
            } else {
                $this->error('保存设置失败！');
            }
        }
    }
}
