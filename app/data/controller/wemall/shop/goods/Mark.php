<?php

declare(strict_types=1);


namespace app\data\controller\wemall\shop\goods;

use app\data\model\wemall\DataWemallGoodsMark;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 商品标签管理.
 * @class Mark
 */
class Mark extends Controller
{
    /**
     * 商品标签管理.
     * @auth true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataWemallGoodsMark::mQuery($this->get)->layTable(function () {
            $this->title = '商品标签管理';
        }, static function (QueryHelper $query) {
            $query->like('name')->equal('status')->dateBetween('create_time');
        });
    }

    /**
     * 添加商品标签.
     * @auth true
     */
    public function add()
    {
        DataWemallGoodsMark::mForm('form');
    }

    /**
     * 编辑商品标签.
     * @auth true
     */
    public function edit()
    {
        DataWemallGoodsMark::mForm('form');
    }

    /**
     * 修改商品标签状态
     * @auth true
     */
    public function state()
    {
        DataWemallGoodsMark::mSave();
    }

    /**
     * 删除商品标签.
     * @auth true
     */
    public function remove()
    {
        DataWemallGoodsMark::mDelete();
    }

    /**
     * 商品标签选择kkd.
     * @login true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function select()
    {
        $this->get['status'] = 1;
        $this->get['deleted'] = 0;
        $this->index();
    }
}
