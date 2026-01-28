<?php

use think\admin\extend\PhinxExtend;
use think\migration\Migrator;

class Data20251216 extends Migrator
{
    public function change()
    {
        $this->_create_data_account_auth(); // 账号授权
        $this->_create_data_account_bind(); // 子账号
        $this->_create_data_account_user(); //
    }

    /**
     * 账号-授权
     * @class DataAccountAuth
     * @table data_account_auth
     * @return void
     */
    private function _create_data_account_auth()
    {
        // 创建数据表对象
        $table = $this->table('data_account_auth', [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '账号-授权',
        ]);
        // 创建或更新数据表
        PhinxExtend::upgrade($table, [
            ['usid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '终端账号']],
            ['time', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '有效时间']],
            ['type', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '授权类型']],
            ['token', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '授权令牌']],
            ['tokenv', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '授权验证']],
            ['create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间']],
            ['update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间']],
        ], [
            'usid', 'type', 'time', 'token', 'create_time',
        ], true);
    }

    /**
     * 账号-终端
     * @class DataAccountBind
     * @table data_account_bind
     * @return void
     */
    private function _create_data_account_bind()
    {
        // 创建数据表对象
        $table = $this->table('data_account_bind', [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '账号-终端',
        ]);
        // 创建或更新数据表
        PhinxExtend::upgrade($table, [
            ['unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '账号编号']],
            ['type', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '终端类型']],
            ['phone', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => '绑定手机']],
            ['appid', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => 'APPID']],
            ['openid', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => 'OPENID']],
            ['unionid', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => 'UnionID']],
            ['headimg', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '用户头像']],
            ['nickname', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '用户昵称']],
            ['password', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '登录密码']],
            ['extra', 'text', ['default' => NULL, 'null' => true, 'comment' => '扩展数据']],
            ['sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重']],
            ['status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '账号状态']],
            ['deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)']],
            ['create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '注册时间']],
            ['update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间']],
        ], [
            'type', 'unid', 'sort', 'phone', 'appid', 'status', 'openid', 'unionid', 'deleted', 'create_time',
        ], true);
    }

    /**
     * 账号-资料
     * @class DataAccountUser
     * @table data_account_user
     * @return void
     */
    private function _create_data_account_user()
    {
        // 创建数据表对象
        $table = $this->table('data_account_user', [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '账号-资料',
        ]);
        // 创建或更新数据表
        PhinxExtend::upgrade($table, [
            ['code', 'string', ['limit' => 16, 'default' => '', 'null' => true, 'comment' => '用户编号']],
            ['phone', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '用户手机']],
            ['email', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '用户邮箱']],
            ['unionid', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => 'UnionID']],
            ['username', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '用户姓名']],
            ['nickname', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '用户昵称']],
            ['password', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '认证密码']],
            ['headimg', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '用户头像']],
            ['region_prov', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '所在省份']],
            ['region_city', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '所在城市']],
            ['region_area', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '所在区域']],
            ['remark', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '备注(内部使用)']],
            ['shop_name', 'string', ['limit' => 255, 'default' => '', 'null' => true, 'comment' => '店名']],
            ['balance', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00', 'comment' => '余额']],
            ['extra', 'text', ['default' => NULL, 'null' => true, 'comment' => '扩展数据']],
            ['sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重']],
            ['status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '用户状态(0拉黑,1正常)']],
            ['deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)']],
            ['create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '注册时间']],
            ['update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间']],
        ], [
            'code', 'sort', 'phone', 'email', 'status', 'unionid', 'deleted', 'username', 'nickname', 'region_prov', 'region_city', 'region_area', 'create_time',
        ], true);
    }

}
