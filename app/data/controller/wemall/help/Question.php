<?php

declare(strict_types=1);


namespace app\data\controller\wemall\help;

use app\data\model\account\DataAccountUser;
use app\data\model\wemall\DataWemallHelpQuestion;
use app\data\model\wemall\DataWemallHelpQuestionX;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\admin\service\AdminService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 工单提问管理
 * class Question.
 */
class Question extends Controller
{
    /**
     * 工单提问管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataWemallHelpQuestion::mQuery()->layTable(function () {
            $this->title = '工单提问管理';
            $this->types = DataWemallHelpQuestion::tStatus;
        }, function (QueryHelper $helper) {
            $helper->with(['bindUser'])->where(['deleted' => 0]);
            $helper->like('name,content')->equal('status')->dateBetween('create_time');
            // 提交用户搜索
            $db = DataAccountUser::mQuery()->like('username')->field('id')->db();
            if ($db->getOptions('where')) {
                $helper->whereRaw("unid in {$db->buildSql()}");
            }
        });
    }

    /**
     * 编辑工单内容.
     * @auth true
     */
    public function edit()
    {
        $this->title = '编辑工单内容';
        DataWemallHelpQuestion::mQuery()->with(['bindUser', 'comments'])->mForm('form');
    }

    /**
     * 修改工单状态
     * @auth true
     */
    public function state()
    {
        DataWemallHelpQuestion::mSave($this->_vali([
            'status.in:0,1' => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除工单数据.
     * @auth true
     */
    public function remove()
    {
        DataWemallHelpQuestion::mDelete();
    }

    /**
     * 表单数据处理.
     */
    protected function _form_filter(array &$data)
    {
        if ($this->request->isPost()) {
            if (empty($data['content'])) {
                $this->error('回复内容不能为空！');
            }
            $data['status'] = 2;
            DataWemallHelpQuestionX::mk()->save([
                'ccid' => $data['id'],
                'content' => $data['content'],
                'reply_by' => AdminService::getUserId(),
            ]);
            unset($data['content']);
        }
    }

    /**
     * 表单结果处理.
     */
    protected function _form_result(bool $state)
    {
        if ($state) {
            $this->success('内容保存成功！', 'javascript:history.back()');
        }
    }
}
