<?php

use think\migration\Migrator;

class ExpandOpenidLength extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // 获取表对象
        $table = $this->table('data_account_bind');
        
        // 修改 openid 字段长度为 128 (原为 50)
        // 以支持 Apple Sign In (User ID 可长达 64+ 字符) 和长邮箱地址
        $table->changeColumn('openid', 'string', ['limit' => 128, 'default' => '', 'null' => true, 'comment' => 'OPENID/账号标识']);
        
        // 保存修改
        $table->update();
    }
}
