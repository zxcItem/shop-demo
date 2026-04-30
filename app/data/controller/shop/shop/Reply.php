<?php

declare(strict_types=1);

namespace app\data\controller\shop\shop;

use app\data\model\account\DataAccountUser;
use app\data\model\shop\DataShopGoods;
use app\data\model\shop\DataShopUserActionComment;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 商品评论管理.
 * @class Reply
 */
class Reply extends Controller
{
    /**
     * 商品评论管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->request->get('type', 'index');
        DataShopUserActionComment::mQuery()->layTable(function () {
            $this->title = '商品评论管理';
        }, function (QueryHelper $query) {
            // 用户查询
            $db = DataAccountUser::mQuery()->like('phone|nickname#user_keys')->db();
            if ($db->getOptions('where')) {
                $query->whereRaw("unid in {$db->field('id')->buildSql()}");
            }
            // 商品查询
            $db = DataShopGoods::mQuery()->like('code|name#goods_keys')->db();
            if ($db->getOptions('where')) {
                $query->whereRaw("gcode in {$db->field('code')->buildSql()}");
            }
            // 数据过滤
            $query->like('order_no')->where(['status' => intval($this->type === 'index'), 'deleted' => 0]);
            $query->with(['bindUser', 'bindGoods'])->dateBetween('create_time');
        });
    }

    /**
     * 修改评论内容.
     * @auth true
     */
    public function edit()
    {
        DataShopUserActionComment::mQuery()->with(['user', 'goods', 'orderinfo'])->mForm('form');
    }

    /**
     * 修改评论状态
     * @auth true
     */
    public function state()
    {
        DataShopUserActionComment::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }
}
