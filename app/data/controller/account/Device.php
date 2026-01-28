<?php

namespace app\data\controller\account;

use app\data\model\account\DataAccountBind;
use app\data\service\Account;
use think\admin\Controller;
use think\admin\helper\QueryHelper;

/**
 * 终端账号管理
 * @class Device
 * @package app\data\controller\user
 */
class Device extends Controller
{
    /**
     * 终端账号管理
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        DataAccountBind::mQuery()->layTable(function () {
            $this->title = '终端账号管理';
            $this->types = Account::types(1);
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->with('user')->equal('type#utype')->like('phone,nickname,username,create_time');
        });
    }

    /**
     * 账号接口配置
     * @auth true
     * @return void
     * @throws \think\admin\Exception
     */
    public function config()
    {
        $this->types = Account::types();
        if ($this->request->isGet()) {
            $this->data = Account::config();
            $this->data['headimg'] = Account::headimg();
            $this->fetch();
        } else {
            // 保存当前参数
            Account::config($this->request->post());
            // 设置接口有效时间及默认头像
            $expire = $this->request->post('expire');
            $headimg = $this->request->post('headimg');
            Account::expire($expire ?: 0, $headimg ?: null);
            // 设置开放接口通道状态
            $types = $this->request->post('types', []);
            foreach ($this->types as $k => $v) {
                Account::set($k, intval(in_array($k, $types)));
            }
            if (Account::save()) {
                $this->success('配置保存成功！');
            } else {
                $this->error('配置保存失败！');
            }
        }
    }

    /**
     * 修改用户状态
     * @auth true
     */
    public function state()
    {
        DataAccountBind::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除终端账号
     * @auth true
     */
    public function remove()
    {
        DataAccountBind::mDelete();
    }
}