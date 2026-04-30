<?php

declare(strict_types=1);

namespace app\data\controller\marketing\coupon;



use app\data\controller\marketing\Base;

/**
 * 优惠券营销设置
 * @class Config
 */
class Config extends Base
{
    protected $menus = [
        ['title' => '营销设置', 'url' => 'data/marketing.coupon.config/index'],
        ['title' => '优惠券列表', 'url' => 'data/marketing.coupon.coupon/index'],
        ['title' => '领取记录', 'url' => 'data/marketing.coupon.record/index'],
    ];

    /**
     * 营销设置
     * @auth true
     * @menu true
     */
    public function index()
    {
        // die('Config Index'); // 调试：检查控制器是否被正确调用
        $this->skey = 'shop_marketing_coupon';
        if ($this->request->isGet()) {
            $this->title = '营销设置';
            // 使用 sysdata 处理 JSON 数据更稳定
            $data = sysdata($this->skey);
            $this->data = array_merge([
                'status'       => '1',
                'max_per_user' => '10',
                'description'  => "1. 优惠券仅限在该商城内使用；\n2. 每个订单仅限使用一张优惠券；\n3. 优惠券过期自动失效。"
            ], is_array($data) ? $data : []);
            $this->fetch();
        } else {
            $post = $this->request->post();
            if (sysdata($this->skey, $post)) {
                $this->success('保存设置成功！');
            } else {
                $this->error('保存设置失败，请稍候再试！');
            }
        }
    }
}
