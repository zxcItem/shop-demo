<?php

declare(strict_types=1);


namespace app\data\controller\shop\base;

use app\data\model\shop\DataShopConfigLevel;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 会员等级管理.
 * @class Level
 */
class Level extends Controller
{
    /**
     * 会员等级管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataShopConfigLevel::mQuery()->layTable(function () {
            $this->title = '会员等级管理';
        }, static function (QueryHelper $query) {
            $query->like('name')->equal('status')->dateBetween('create_time');
        });
    }

    /**
     * 添加会员等级.
     * @auth true
     * @throws DbException
     */
    public function add()
    {
        $this->max = DataShopConfigLevel::maxNumber() + 1;
        DataShopConfigLevel::mForm('form');
    }

    /**
     * 编辑会员等级.
     * @auth true
     * @throws DbException
     */
    public function edit()
    {
        $this->max = DataShopConfigLevel::maxNumber();
        DataShopConfigLevel::mForm('form');
    }

    /**
     * 表单结果处理.
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function _form_result(bool $state)
    {
        if ($state) {
            $isasc = input('old_number', 0) <= input('number', 0);
            $order = $isasc ? 'number asc,utime asc' : 'number asc,utime desc';
            foreach (DataShopConfigLevel::mk()->order($order)->select() as $number => $upgrade) {
                $upgrade->save(['number' => $number]);
            }
        }
    }

    /**
     * 修改等级状态
     * @auth true
     */
    public function state()
    {
        DataShopConfigLevel::mSave();
    }

    /**
     * 删除会员等级.
     * @auth true
     */
    public function remove()
    {
        DataShopConfigLevel::mDelete();
    }

    /**
     * 表单数据处理.
     * @throws DbException
     */
    protected function _form_filter(array &$vo)
    {
        if (empty($vo['extra'])) {
            $vo['extra'] = [];
        }
        if ($this->request->isGet()) {
            $vo['number'] = $vo['number'] ?? DataShopConfigLevel::maxNumber();
        } else {
            $vo['utime'] = time();
            if (empty($vo['number'])) {
                $vo['extra'] = ['enter_vip_status' => 0, 'order_amount_status' => 0, 'order_amount_number' => 0];
            } else {
                $count = $vo['extra']['enter_vip_status'] = empty($vo['extra']['enter_vip_status']) ? 0 : 1;
                if (empty($vo['extra']['order_amount_status']) || bccomp(strval($vo['extra']['order_amount_number']), '0.00', 2) <= 0) {
                    $vo['extra'] = array_merge($vo['extra'], ['order_amount_status' => 0, 'order_amount_number' => 0]);
                } else {
                    ++$count;
                    $vo['extra']['order_amount_status'] = 1;
                }
                if (empty($count)) {
                    $this->error('升级条件不能为空！');
                }
            }
        }
    }

    /**
     * 状态变更处理.
     * @auth true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _save_result()
    {
        $this->_form_result(true);
    }

    /**
     * 删除结果处理.
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _delete_result()
    {
        $this->_form_result(true);
    }
}
