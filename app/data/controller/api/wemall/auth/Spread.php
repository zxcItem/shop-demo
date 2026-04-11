<?php

declare(strict_types=1);

namespace app\data\controller\api\wemall\auth;

use app\data\model\account\DataAccountUser;
use app\data\controller\api\wemall\Auth;
use app\data\model\wemall\DataWemallConfigPoster;
use app\data\model\wemall\DataWemallUserRelation;
use app\data\service\wemall\PosterService;
use app\data\service\wemall\UserUpgrade;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;
use WeChat\Exceptions\InvalidResponseException;
use WeChat\Exceptions\LocalCacheException;

/**
 * 推广用户管理.
 * @class Spread
 */
class Spread extends Auth
{
    /**
     * 获取我推广的用户.
     */
    public function get()
    {
        DataWemallUserRelation::mQuery(null, function (QueryHelper $query) {
            // 用户搜索查询
            $db = DataAccountUser::mQuery()->like('phone|nickname#keys')->db();
            if ($db->getOptions('where')) {
                $query->whereRaw("unid in {$db->field('id')->buildSql()}");
            }
            // 数据条件查询
            $query->with(['bindUser'])->where(['puid1' => $this->unid])->order('id desc');
            $this->success('获取数据成功！', $query->page(intval(input('page', 1)), false, false, 10));
        });
    }

    /**
     * 临时绑定推荐人.
     */
    public function bind()
    {
        try {
            $input = $this->_vali(['from.require' => '推荐人不能为空！']);
            $relation = UserUpgrade::bindAgent($this->relation, intval($input['from']), 0);
            $this->success('绑定推荐人成功！', $relation->toArray());
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 获取我的海报.
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws \think\admin\Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function poster()
    {
        $account = $this->account->get();
        $extra = [
            'user.spreat' => '/pages/home/index?from=UNID&fuser=CODE',
            'user.headimg' => $account['user']['headimg'] ?? '',
            'user.nickname' => $account['user']['nickname'] ?? '',
            'user.rolename' => $this->relation->getAttr('level_name'),
        ];
        $items = DataWemallConfigPoster::items($this->levelCode, $this->type);
        foreach ($items as &$item) {
            $item['image'] = PosterService::create($item['image'], $item['content'], $extra);
            unset($item['content']);
        }
        $this->success('获取海报成功！', $items);
    }
}
