<?php

declare(strict_types=1);

namespace app\data\controller\marketing\coupon;

use app\data\controller\marketing\Base;
use app\data\model\shop\DataShopConfigCoupon;
use app\data\model\shop\DataShopConfigLevel;
use think\admin\helper\QueryHelper;

/**
 * 营销优惠券管理
 * @class Coupon
 */
class Coupon extends Base
{
    protected $menus = [
        ['title' => '营销设置', 'url' => 'data/marketing.coupon.config/index'],
        ['title' => '优惠券列表', 'url' => 'data/marketing.coupon.coupon/index'],
        ['title' => '领取记录', 'url' => 'data/marketing.coupon.record/index'],
    ];

    /**
     * 优惠券管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '优惠券管理';
        DataShopConfigCoupon::mQuery()->layTable(function () {
            $this->types = DataShopConfigCoupon::types;
        }, function (QueryHelper $query) {
            $query->like('name,remark')->equal('status,type');
            $query->dateBetween('create_time')->where(['deleted' => 0]);
        });
    }

    /**
     * 添加抵扣卡券.
     * @auth true
     */
    public function add()
    {
        $this->title = '添加抵扣卡券';
        DataShopConfigCoupon::mForm('form');
    }

    /**
     * 编辑抵扣卡券.
     * @auth true
     */
    public function edit()
    {
        $this->title = '编辑抵扣卡券';
        DataShopConfigCoupon::mForm('form');
    }

    /**
     * 修改抵扣卡券.
     * @auth true
     */
    public function state()
    {
        DataShopConfigCoupon::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除抵扣卡券.
     * @auth true
     */
    public function remove()
    {
        DataShopConfigCoupon::mDelete();
    }

    /**
     * 表单数据处理.
     */
    protected function _form_filter(array &$data)
    {
        if ($this->request->isGet()) {
            $this->types = DataShopConfigCoupon::types;
            $this->levels = DataShopConfigLevel::items();
            array_unshift($this->levels, ['name' => '全部', 'number' => '-']);
        } else {
            $data['levels'] = arr2str($data['levels'] ?? []);
        }
    }

    /**
     * 表单结果处理.
     */
    protected function _form_result(bool $result)
    {
        if ($result) {
            $this->success('卡券保存成功！', 'javascript:history.back()');
        } else {
            $this->error('卡券保存失败！');
        }
    }
}
