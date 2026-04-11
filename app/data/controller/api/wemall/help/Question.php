<?php

declare(strict_types=1);


namespace app\data\controller\wemall\help;


use app\data\controller\api\wemall\Auth;
use app\data\model\wemall\DataWemallHelpQuestion;
use app\data\model\wemall\DataWemallHelpQuestionX;
use think\admin\Exception;
use think\admin\helper\QueryHelper;
use think\admin\Storage;

/**
 * 用户工单接口.
 * @class Question
 */
class Question extends Auth
{
    /**
     * 获取工单数据.
     */
    public function get()
    {
        DataWemallHelpQuestion::mQuery(null, function (QueryHelper $query) {
            if (input('id', 0) > 0) {
                $query->with(['comments']);
            }
            $query->withoutField('sort,deleted,deleted_time');
            $query->equal('id,reply_st')->like('name');
            $query->where(['unid' => $this->unid, 'status' => [1, 2, 3, 4], 'deleted' => 0]);
            $sort = ['new' => 'id desc', 'hot' => 'num_read desc'][input('sort', '_')] ?? 'sort desc,id desc';
            $this->success('获取工单数据', $query->order($sort)->page(true, false, false, 10));
        });
    }

    /**
     * 提交问题数据.
     * @throws Exception
     */
    public function set()
    {
        $data = $this->_vali([
            'unid.value' => $this->unid,
            'name.require' => '问题不能为空!',
            'phone.default' => '',
            'images.default' => '',
            'content.require' => '描述不能为空!',
        ]);
        if (!empty($data['images'])) {
            $images = explode('|', $data['images']);
            foreach ($images as &$image) {
                $image = Storage::saveImage($image, 'feedback')['url'];
            }
            $data['images'] = implode('|', $images);
        }
        if (($model = DataWemallHelpQuestion::mk())->save($data)) {
            $this->success('提交成功！', $model->toArray());
        } else {
            $this->error('提交失败！');
        }
    }

    /**
     * 回复工单内容.
     * @throws Exception
     */
    public function reply()
    {
        $data = $this->_vali([
            'unid.value' => $this->unid,
            'ccid.require' => '编号不能为空！',
            'images.default' => '',
            'status.value' => 3,
            'content.require' => '描述不能为空!',
        ]);
        if (!empty($data['images'])) {
            $images = explode('|', $data['images']);
            foreach ($images as &$image) {
                $image = Storage::saveImage($image, 'feedback')['url'];
            }
            $data['images'] = implode('|', $images);
        }
        if (DataWemallHelpQuestionX::mk()->save($data)) {
            $this->success('提交成功！');
        } else {
            $this->error('提交失败！');
        }
    }

    /**
     * 确认工单已解决.
     */
    public function confirm()
    {
        $data = $this->_vali(['ccid.require' => '编号不能为空！']);
        $question = DataWemallHelpQuestion::mk()->findOrEmpty($data['ccid']);
        if ($question->isEmpty() || $question->getAttr('unid') !== $this->unid) {
            $this->error('无效工单！');
        }
        $question->save(['status' => 4]);
        DataWemallHelpQuestionX::mk()->save([
            'unid' => $this->unid, 'status' => 4,
            'ccid' => $data['ccid'], 'content' => '已主动确认完成！',
        ]);
        $this->success('确认成功！');
    }
}
