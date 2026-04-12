<?php

declare(strict_types=1);

namespace app\data\controller\api\wemall\help;

use app\data\model\wemall\DataWemallHelpProblem;
use think\admin\Controller;
use think\admin\helper\QueryHelper;

/**
 * 常见问题数据接口.
 * @class Problem
 */
class Problem extends Controller
{
    /**
     * 获取反馈意见
     */
    public function get()
    {
        DataWemallHelpProblem::mQuery(null, function (QueryHelper $query) {
            $query->like('name')->equal('id');
            $this->success('获取反馈意见', $query->order('sort desc,id desc')->page(true, false, false, 10));
        });
    }
}
