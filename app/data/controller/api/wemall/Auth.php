<?php

declare(strict_types=1);


namespace app\data\controller\api\wemall;

use app\data\controller\api\Auth as AccountAuth;
use app\data\model\wemall\DataWemallUserRelation;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 基础授权控制器.
 * @class Auth
 */
abstract class Auth extends AccountAuth
{
    /**
     * 用户关系.
     * @var DataWemallUserRelation
     */
    protected $relation;

    /**
     * 等级序号.
     * @var int
     */
    protected $levelCode;

    /**
     * 控制器初始化.
     */
    protected function initialize()
    {
        try {
            parent::initialize();
            $this->checkUserStatus()->withUserRelation();
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 初始化当前用户.
     * @return static
     * @throws \think\admin\Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function withUserRelation(): Auth
    {
        $this->relation = DataWemallUserRelation::withInit($this->unid);
        $this->levelCode = intval($this->relation->getAttr('level_code'));
        return $this;
    }
}
