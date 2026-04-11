<?php

declare(strict_types=1);

namespace app\data\service\wemall;

use app\data\model\account\DataAccountUser;
use app\data\model\wemall\DataWemallOrderCart;
use app\data\model\wemall\DataWemallOrderItem;
use app\data\model\wemall\DataWemallUserActionCollect;
use app\data\model\wemall\DataWemallUserActionComment;
use app\data\model\wemall\DataWemallUserActionHistory;
use think\admin\Exception;
use think\admin\Storage;
use think\db\exception\DbException;

/**
 * 用户行为数据服务
 * @class UserAction
 */
abstract class UserAction
{
    /**
     * 设置行为数据.
     * @param int $unid 用户编号
     * @param string $gcode 商品编号
     * @param string $type 行为类型
     * @throws DbException
     */
    public static function set(int $unid, string $gcode, string $type): array
    {
        $data = ['unid' => $unid, 'gcode' => $gcode];
        if ($type === 'collect') {
            $model = DataWemallUserActionCollect::mk()->where($data)->findOrEmpty();
        } else {
            $model = DataWemallUserActionHistory::mk()->where($data)->findOrEmpty();
        }
        $data['sort'] = time();
        $data['times'] = $model->isExists() ? $model->getAttr('times') + 1 : 1;
        $model->save($data) && UserAction::recount($unid);
        return $model->toArray();
    }

    /**
     * 删除行为数据.
     * @param int $unid 用户编号
     * @param string $gcode 商品编号
     * @param string $type 行为类型
     * @throws DbException
     */
    public static function del(int $unid, string $gcode, string $type): array
    {
        $data = [['unid', '=', $unid], ['gcode', 'in', str2arr($gcode)]];
        if ($type === 'collect') {
            DataWemallUserActionCollect::mk()->where($data)->delete();
        } else {
            DataWemallUserActionHistory::mk()->where($data)->delete();
        }
        self::recount($unid);
        return $data;
    }

    /**
     * 清空行为数据.
     * @param int $unid 用户编号
     * @param string $type 行为类型
     * @throws DbException
     */
    public static function clear(int $unid, string $type): array
    {
        $data = [['unid', '=', $unid]];
        if ($type === 'collect') {
            DataWemallUserActionCollect::mk()->where($data)->delete();
        } else {
            DataWemallUserActionHistory::mk()->where($data)->delete();
        }
        self::recount($unid);
        return $data;
    }

    /**
     * 刷新用户行为统计
     * @param int $unid 用户编号
     * @param null|array $data 非数组时更新数据
     * @return array [collect, history, mycarts]
     * @throws DbException
     */
    public static function recount(int $unid, ?array &$data = null): array
    {
        $isUpdate = !is_array($data);
        if ($isUpdate) {
            $data = [];
        }
        // 更新收藏及足迹数量和购物车
        $map = ['unid' => $unid];
        $data['mycarts_total'] = DataWemallOrderCart::mk()->where($map)->sum('number');
        $data['collect_total'] = DataWemallUserActionCollect::mk()->where($map)->count();
        $data['history_total'] = DataWemallUserActionHistory::mk()->where($map)->count();
        if ($isUpdate && ($user = DataAccountUser::mk()->findOrEmpty($unid))->isExists()) {
            $user->save(['extra' => array_merge($user->getAttr('extra'), $data)]);
        }
        return [$data['collect_total'], $data['history_total'], $data['mycarts_total']];
    }

    /**
     * 写入商品评论.
     * @param float|string $rate
     * @throws Exception
     */
    public static function comment(DataWemallOrderItem $item, $rate, string $content, string $images): bool
    {
        // 图片上传转存
        if (!empty($images)) {
            $images = explode('|', $images);
            foreach ($images as &$image) {
                $image = Storage::saveImage($image, 'comment')['url'];
            }
            $images = join('|', $images);
        }
        // 根据单号+商品规格查询评论
        $code = md5("{$item->getAttr('order_no')}#{$item->getAttr('ghash')}");
        return DataWemallUserActionComment::mk()->where(['code' => $code])->findOrEmpty()->save([
            'code' => $code,
            'unid' => $item->getAttr('unid'),
            'gcode' => $item->getAttr('gcode'),
            'ghash' => $item->getAttr('ghash'),
            'order_no' => $item->getAttr('order_no'),
            'rate' => $rate,
            'images' => $images,
            'content' => $content,
        ]);
    }
}
