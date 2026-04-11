<?php

declare(strict_types=1);


namespace app\data\controller\wemall\shop\goods;

use app\data\model\wemall\DataWemallGoodsCate;
use think\admin\Controller;
use think\admin\extend\DataExtend;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 商品分类管理.
 * @class Cate
 */
class Cate extends Controller
{
    /**
     * 最大级别.
     * @var int
     */
    protected $maxLevel = 3;

    /**
     * 商品分类管理.
     * @auth true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataWemallGoodsCate::mQuery($this->get)->layTable(function () {
            $this->title = '商品分类管理';
        }, static function (QueryHelper $query) {
            $query->where(['deleted' => 0]);
            $query->like('name')->equal('status')->dateBetween('create_time');
        });
    }

    /**
     * 添加商品分类.
     * @auth true
     */
    public function add()
    {
        DataWemallGoodsCate::mForm('form');
    }

    /**
     * 编辑商品分类.
     * @auth true
     */
    public function edit()
    {
        DataWemallGoodsCate::mForm('form');
    }

    /**
     * 修改商品分类状态
     * @auth true
     */
    public function state()
    {
        DataWemallGoodsCate::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除商品分类.
     * @auth true
     */
    public function remove()
    {
        DataWemallGoodsCate::mDelete();
    }

    /**
     * 商品分类选择器.
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

    /**
     * 列表数据处理.
     */
    protected function _page_filter(array &$data)
    {
        $data = DataExtend::arr2table($data);
    }

    /**
     * 表单数据处理.
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function _form_filter(array &$data)
    {
        if ($this->request->isGet()) {
            $data['pid'] = intval($data['pid'] ?? input('pid', '0'));
            $this->cates = DataWemallGoodsCate::pdata($this->maxLevel, $data, [
                'id' => '0', 'pid' => '-1', 'name' => '顶部分类',
            ]);
        }
    }
}
