<?php

declare(strict_types=1);

namespace app\data\controller\marketing\index;

use think\admin\Controller;

/**
 * 营销中心管理
 * @class Index
 * @package app\data\controller\shop
 */
class Index extends Controller
{
    /**
     * 营销中心首页
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '营销中心';
        $this->modules = [
            [
                'title' => '优惠券管理',
                'desc'  => '提升下单转化率及复购率。',
                'icon'  => 'layui-icon-ticket',
                'color' => 'linear-gradient(135deg, #3b82f6, #60a5fa)',
                'url'   => 'data/marketing.coupon.config/index',
                'bg'    => '🎟️'
            ],
            [
                'title' => '限时秒杀',
                'desc'  => '低价吸引眼球，引爆流量。',
                'icon'  => 'layui-icon-fire',
                'color' => 'linear-gradient(135deg, #f43f5e, #fb7185)',
                'url'   => 'data/marketing.seckill/index',
                'bg'    => '⚡'
            ],
            [
                'title' => '多人拼团',
                'desc'  => '社交裂变，用户几何增长。',
                'icon'  => 'layui-icon-group',
                'color' => 'linear-gradient(135deg, #8b5cf6, #a78bfa)',
                'url'   => 'data/marketing.group/index',
                'bg'    => '👥'
            ],
            [
                'title' => '积分商城',
                'desc'  => '完善积分生态，增强粘性。',
                'icon'  => 'layui-icon-diamond',
                'color' => 'linear-gradient(135deg, #f59e0b, #fbbf24)',
                'url'   => 'data/marketing.integral.config/index',
                'bg'    => '🪙'
            ],
            [
                'title' => '满减送',
                'desc'  => '设定门槛，提升平均客单价。',
                'icon'  => 'layui-icon-gift',
                'color' => 'linear-gradient(135deg, #10b981, #34d399)',
                'url'   => 'data/marketing.full/index',
                'bg'    => '🎁'
            ],
            [
                'title' => '分销管理',
                'desc'  => '全员分销，裂变获客增收。',
                'icon'  => 'layui-icon-share',
                'color' => 'linear-gradient(135deg, #0ea5e9, #38bdf8)',
                'url'   => 'data/marketing.share/index',
                'bg'    => '🔗'
            ]
        ];
        $this->fetch();
    }
}
