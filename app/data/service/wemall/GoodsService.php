<?php

declare(strict_types=1);

namespace app\data\service\wemall;

use app\data\model\wemall\DataWemallGoods;
use app\data\model\wemall\DataWemallGoodsItem;
use app\data\model\wemall\DataWemallGoodsStock;
use app\data\model\wemall\DataWemallOrder;
use app\data\model\wemall\DataWemallOrderCart;
use app\data\model\wemall\DataWemallOrderItem;
use think\admin\Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

/**
 * 商品数据服务
 * @class GoodsService
 */
abstract class GoodsService
{
    /**
     * 更新商品库存数据.
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function stock(string $code): bool
    {
        // 入库统计
        $query = DataWemallGoodsStock::mk()->field('ghash,ifnull(sum(gstock),0) stock_total');
        $stockList = $query->where(['gcode' => $code])->group('gcode,ghash')->select()->toArray();
        // 销量统计
        $query = DataWemallOrder::mk()->alias('a')->field('b.ghash,ifnull(sum(b.stock_sales),0) stock_sales');
        $query->join([DataWemallOrderItem::mk()->getTable() => 'b'], 'a.order_no=b.order_no', 'left');
        $query->where([['b.gcode', '=', $code], ['a.status', '>', 0], ['a.deleted_status', '=', 0]]);
        $salesList = $query->group('b.ghash')->select()->toArray();
        // 组装数据
        $items = [];
        foreach (array_merge($stockList, $salesList) as $vo) {
            $key = $vo['ghash'];
            $items[$key] = array_merge($items[$key] ?? [], $vo);
            if (empty($items[$key]['stock_sales'])) {
                $items[$key]['stock_sales'] = 0;
            }
            if (empty($items[$key]['stock_total'])) {
                $items[$key]['stock_total'] = 0;
            }
        }
        unset($salesList, $stockList);
        // 更新商品规格销量及库存
        foreach ($items as $hash => $item) {
            DataWemallGoodsItem::mk()->where(['ghash' => $hash])->update([
                'stock_total' => $item['stock_total'], 'stock_sales' => $item['stock_sales'],
            ]);
        }
        // 更新商品主体销量及库存
        DataWemallGoods::mk()->where(['code' => $code])->update([
            'stock_total' => intval(array_sum(array_column($items, 'stock_total'))),
            'stock_sales' => intval(array_sum(array_column($items, 'stock_sales'))),
            'stock_virtual' => DataWemallGoodsItem::mk()->where(['gcode' => $code])->sum('number_virtual'),
        ]);
        return true;
    }

    /**
     * 解析下单数据.
     * @param int $unid 用户编号
     * @param string $rules 直接下单
     * @param string $carts 购物车下单
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function parse(int $unid, string $rules, string $carts): array
    {
        // 读取商品数据
        [$lines, $carts] = [[], str2arr($carts)];
        if (!empty($carts)) {
            $where = [['unid', '=', $unid], ['id', 'in', $carts]];
            $field = ['ghash' => 'ghash', 'gcode' => 'gcode', 'gspec' => 'gspec', 'number' => 'count'];
            DataWemallOrderCart::mk()->field($field)->where($where)->with([
                'goods' => function ($query) {
                    $query->where(['status' => 1, 'deleted' => 0]);
                    $query->withoutField(['specs', 'content', 'status', 'deleted', 'create_time', 'update_time']);
                },
                'specs' => function ($query) {
                    $query->where(['status' => 1]);
                    $query->withoutField(['status', 'create_time', 'update_time']);
                },
            ])->select()->each(function (Model $model) use (&$lines) {
                if (isset($lines[$ghash = $model->getAttr('ghash')])) {
                    $lines[$ghash]['count'] += $model->getAttr('count');
                } else {
                    $lines[$ghash] = $model->toArray();
                }
            });
        } elseif (!empty($rules)) {
            foreach (explode(';', $rules) as $rule) {
                [$ghash, $count] = explode(':', "{$rule}:1");
                if (isset($lines[$ghash])) {
                    $lines[$ghash]['count'] += $count;
                } else {
                    $lines[$ghash] = ['ghash' => $ghash, 'gcode' => '', 'gspec' => '', 'count' => $count];
                }
            }
            // 读取规格数据
            $map1 = [['status', '=', 1], ['ghash', 'in', array_column($lines, 'ghash')]];
            foreach (DataWemallGoodsItem::mk()->where($map1)->select()->toArray() as $item) {
                foreach ($lines as &$line) {
                    if ($line['ghash'] === $item['ghash']) {
                        [$line['gcode'], $line['gspec'], $line['specs']] = [$item['gcode'], $item['gspec'], $item];
                    }
                }
            }
            // 读取商品数据
            $map2 = [['status', '=', 1], ['deleted', '=', 0], ['code', 'in', array_unique(array_column($lines, 'gcode'))]];
            foreach (DataWemallGoods::mk()->where($map2)->withoutField(['specs', 'content'])->select()->toArray() as $goods) {
                foreach ($lines as &$line) {
                    if ($line['gcode'] === $goods['code']) {
                        $line['goods'] = $goods;
                    }
                }
            }
        } else {
            throw new Exception('无效参数数据！');
        }
        return array_values($lines);
    }
}
