<?php

declare(strict_types=1);


namespace app\data\controller\wemall\base\express;

use app\data\model\wemall\DataWemallExpressCompany;
use app\data\service\wemall\ExpressService;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 快递公司管理.
 * @class Company
 */
class Company extends Controller
{
    /**
     * 快递公司管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        DataWemallExpressCompany::mQuery()->layTable(function () {
            $this->title = '快递公司管理';
        }, function (QueryHelper $query) {
            $query->like('name,code')->equal('status')->dateBetween('create_time');
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
        });
    }

    /**
     * 添加快递公司.
     * @auth true
     */
    public function add()
    {
        $this->title = '添加快递公司';
        DataWemallExpressCompany::mForm('form');
    }

    /**
     * 编辑快递公司.
     * @auth true
     */
    public function edit()
    {
        $this->title = '编辑快递公司';
        DataWemallExpressCompany::mForm('form');
    }

    /**
     * 修改快递公司状态
     * @auth true
     */
    public function state()
    {
        DataWemallExpressCompany::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除快递公司.
     * @auth true
     */
    public function remove()
    {
        DataWemallExpressCompany::mDelete();
    }

    /**
     * 同步快递公司.
     * @auth true
     */
    public function sync()
    {
        try {
            $result = ExpressService::company();
            if (empty($result['code'])) {
                $this->error($result['info']);
            }
            foreach ($result['data'] as $vo) {
                DataWemallExpressCompany::mUpdate([
                    'name' => $vo['title'], 'code' => $vo['code_2'], 'deleted' => 0,
                ], 'code');
            }
            $this->success('同步快递公司成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error('同步快递公司数据失败！');
        }
    }
}
