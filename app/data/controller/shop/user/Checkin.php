<?php


declare(strict_types=1);

namespace app\data\controller\shop\user;

use app\data\model\account\DataAccountUser;
use app\data\model\shop\DataShopUserCheckin;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户签到管理.
 * @class Checkin
 */
class Checkin extends Controller
{
    /**
     * 用户签到管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataShopUserCheckin::mQuery()->layTable(function () {
            $this->title = '用户签到管理';
        }, function (QueryHelper $query) {
            // 搜索数据表字段搜索
            $query->with('user')->dateBetween('create_time');
            // 按用户资料搜索
            $user = DataAccountUser::mQuery()->like('nickname|phone#user');
            if ($user->getOptions('where')) {
                $query->whereRaw("unid in {$user->field('id')->buildSql()}");
            }
        });
    }

    /**
     * 签到配置管理.
     * @auth true
     * @throws Exception
     */
    public function config()
    {
        if ($this->request->isGet()) {
            $this->title = '签到参数配置';
            $this->data = sysdata(DataShopUserCheckin::$ckcfg);
            $this->fetch();
        } elseif ($this->request->isPost()) {
            $this->data = $this->request->post();
            $this->data['items'] = json_decode($this->data['items'] ?? '{}', true);
            sysdata(DataShopUserCheckin::$ckcfg, $this->data);
            $this->success('配置修改成功！', 'javascript:history.back()');
        }
    }
}
