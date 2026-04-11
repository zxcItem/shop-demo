<?php

declare(strict_types=1);

namespace app\data\controller\wemall\user;

use app\data\model\account\DataAccountUser;
use app\data\service\payment\Balance;
use app\data\model\wemall\DataWemallUserRecharge;
use think\admin\Controller;
use think\admin\extend\CodeExtend;
use think\admin\helper\QueryHelper;
use think\admin\service\AdminService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 会员充值管理.
 * @class Recharge
 */
class Recharge extends Controller
{
    /**
     * 会员充值管理.
     * @menu true
     * @auth true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataWemallUserRecharge::mQuery()->layTable(function () {
            $this->title = '会员充值管理';
            $this->total = DataWemallUserRecharge::mk()->where(['deleted' => 0])->sum('amount');
        }, function (QueryHelper $query) {
            // 按会员资料搜索
            $user = DataAccountUser::mQuery()->like('nickname|phone#user');
            if ($user->getOptions('where')) {
                $query->whereRaw("unid in {$user->field('id')->buildSql()}");
            }
            // 搜索数据表字段搜索
            $query->where(['deleted' => 0])->with('user');
            $query->like('name|remark#text')->dateBetween('create_time');
        });
    }

    /**
     * 会员充值余额.
     * @auth true
     */
    public function add()
    {
        DataWemallUserRecharge::mForm('form');
    }

    /**
     * 取消余额充值
     * @auth true
     */
    public function remove()
    {
        try {
            $data = $this->_vali(['id.require' => '数据不为空！']);
            $recharge = DataWemallUserRecharge::mk()->where($data)->findOrEmpty();
            if ($recharge->isEmpty()) {
                $this->error('待删除记录不存在！');
            }
            $this->app->db->transaction(function () use ($recharge) {
                $recharge->save([
                    'deleted' => 1,
                    'deleted_by' => AdminService::getUserId(),
                    'deleted_time' => date('Y-m-d H:i:s'),
                ]);
                Balance::cancel($recharge->getAttr('code'));
            });
            $this->success('取消充值成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 表单回调处理.
     */
    protected function _form_filter(array &$data)
    {
        if (empty($data['code'])) {
            $data['code'] = CodeExtend::uniqidNumber(16, 'B');
        }
        if ($this->request->isGet()) {
            $data['unid'] = $data['unid'] ?? input('unid', 0);
            $this->user = DataAccountUser::mk()->findOrEmpty($data['unid']);
            $this->user->isEmpty() && $this->error('无效用户信息！');
        } else {
            try {
                $data = $this->_vali([
                    'name.default' => '后台余额操作',
                    'code.require' => '单号不能为空！',
                    'unid.require' => '用户不能为空！',
                    'amount.require' => '金额不能为空！',
                    'remark.require' => '描述不能为空！',
                ], $data);
                if (bccomp(strval($data['amount']), '0.00', 2) <= 0) {
                    $this->error('充值金额不能为零！');
                }
                $this->app->db->transaction(static function () use ($data) {
                    $data['create_by'] = AdminService::getUserId();
                    // 创建充值记录
                    DataWemallUserRecharge::mk()->where(['code' => $data['code']])->findOrEmpty()->save($data);
                    // 创建余额变更
                    Balance::create(intval($data['unid']), $data['code'], $data['name'], strval($data['amount']), $data['remark'], true);
                });
                $this->success('余额充值成功！');
            } catch (HttpResponseException $exception) {
                throw $exception;
            } catch (\Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }
}
