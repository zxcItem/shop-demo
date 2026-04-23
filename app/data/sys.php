<?php

declare (strict_types=1);


use app\data\model\account\DataAccountUser;
use app\data\model\payment\DataPaymentRecord;
use app\data\model\wemall\DataWemallOrder;
use app\data\model\wemall\DataWemallUserRelation;
use app\data\service\wemall\UserOrder;
use app\data\service\wemall\UserRebate;
use app\data\service\wemall\UserUpgrade;
use think\Console;
use think\admin\Library;
use think\Request;

if (Library::$sapp->request->isCli()) {
    // 动态注册操作指令
    Console::starting(function (Console $console) {

    });
}else{

// 注册时填写推荐时检查
    $this->app->middleware->add(function (Request $request, \Closure $next) {
        $input = $request->post(['from', 'phone', 'fphone']);
        if (!empty($input['phone']) && !empty($input['fphone'])) {
            $showError = static function ($message, array $data = []) {
                throw new HttpResponseException(json(['code' => 0, 'info' => lang($message), 'data' => $data]));
            };
            $where = ['deleted' => 0];
            if (preg_match('/^1\d{10}$/', $input['fphone'])) {
                $where['phone'] = $input['fphone'];
            } else {
                if (empty($input['from'])) {
                    $showError('无效推荐人');
                }
                $where['id'] = $input['from'];
            }
            // 判断推荐人是否可
            $from = DataAccountUser::mk()->where($where)->findOrEmpty();
            if ($from->isEmpty()) {
                $showError('无效邀请人！');
            }
            if ($from->getAttr('phone') == $input['phone']) {
                $showError('不能邀请自己！');
            }
            [$rela] = DataWemallUserRelation::withRelation($from->getAttr('id'));
            if (empty($rela['entry_agent'])) {
                $showError('无邀请权限！');
            }
            // 检查自己是否已绑定
            $where = ['phone' => $input['phone'], 'deleted' => 0];
            if (($user = DataAccountUser::mk()->where($where)->findOrEmpty())->isExists()) {
                [$rela] = DataWemallUserRelation::withRelation($user->getAttr('id'));
                if (!empty($rela['puid1']) && $rela['puid1'] != $from->getAttr('id')) {
                    $showError('该用户已注册');
                }
            }
        }
        return $next($request);
    }, 'route');

    // 注册用户绑定事件
     Library::$sapp->event->listen('PluginAccountBind', function (array $data) {
        $this->app->log->notice("Event PluginAccountBind {$data['unid']}#{$data['usid']}");
        // 初始化用户关系数据
        DataWemallUserRelation::withInit(intval($data['unid']));
        // 尝试临时绑定推荐人用户
        $input = $this->app->request->post(['from', 'phone', 'fphone']);
        if (!empty($input['fphone'])) {
            try {
                $map = ['deleted' => 0];
                if (preg_match('/^1\d{10}$/', $input['fphone'])) {
                    $map['phone'] = $input['fphone'];
                } else {
                    $map['id'] = $input['from'] ?? 0;
                }
                $from = DataAccountUser::mk()->where($map)->value('id');
                if ($from > 0) {
                    UserUpgrade::bindAgent(intval($data['unid']), $from, 0);
                }
            } catch (\Exception $exception) {
                trace_file($exception);
            }
        }
    });

    // 注册支付审核事件
     Library::$sapp->event->listen('PluginPaymentAudit', function (DataPaymentRecord $payment) {
        $this->app->log->notice("Event PluginPaymentAudit {$payment->getAttr('order_no')}");
        UserOrder::change($payment->getAttr('order_no'), $payment);
    });

    // 注册支付拒审事件
     Library::$sapp->event->listen('PluginPaymentRefuse', function (DataPaymentRecord $payment) {
        $this->app->log->notice("Event PluginPaymentRefuse {$payment->getAttr('order_no')}");
        UserOrder::change($payment->getAttr('order_no'), $payment);
    });

    // 注册支付完成事件
     Library::$sapp->event->listen('PluginPaymentSuccess', function (DataPaymentRecord $payment) {
        $this->app->log->notice("Event PluginPaymentSuccess {$payment->getAttr('order_no')}");
        UserOrder::change($payment->getAttr('order_no'), $payment);
    });

    // 注册支付取消事件
     Library::$sapp->event->listen('PluginPaymentCancel', function (DataPaymentRecord $payment) {
        $this->app->log->notice("Event PluginPaymentCancel {$payment->getAttr('order_no')}");
        UserOrder::change($payment->getAttr('order_no'), $payment);
    });

    // 注册订单确认事件
     Library::$sapp->event->listen('PluginPaymentConfirm', function (array $data) {
        $this->app->log->notice("Event PluginPaymentConfirm {$data['order_no']}");
        UserRebate::confirm($data['order_no']);
    });

    // 订单确认收货事件
     Library::$sapp->event->listen('PluginWemallOrderConfirm', function (DataWemallOrder $order) {
        $this->app->log->notice("Event PluginWemallOrderConfirm {$order->getAttr('order_no')}");
        UserOrder::confirm($order);
    });

}

if (!function_exists('show_gspec')) {
    /**
     * 商品规格过滤显示.
     * @param string $spec 原规格内容
     */
    function show_gspec(string $spec): string
    {
        $specs = [];
        foreach (explode(';;', $spec) as $sp) {
            $specs[] = explode('::', $sp)[1];
        }
        return join(' ', $specs);
    }
}

if (!function_exists('formatdate')) {
    /**
     * 日期格式过滤.
     */
    function formatdate(?string $value): ?string
    {
        return is_string($value) ? str_replace(['年', '月', '日'], ['-', '-', ''], $value) : $value;
    }
}


if (!function_exists('processdate')) {
    /**
     * 日期格式处理
     * @param string|null $value
     * @return string
     */
    function processdate(?string $value): string
    {
        if (empty($value)) return '';
        // 处理中文日期格式 "2025年12月31日 13:50:37" -> "2025-12-31 13:50:37"
        $value = str_replace(['年', '月', '日'], ['-', '-', ''], $value);
        $timestamp = strtotime($value);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');
        if ($timestamp >= $today) {
            return '今天 ' . date('H:i', $timestamp);
        } elseif ($timestamp >= $yesterday) {
            return '昨天 ' . date('H:i', $timestamp);
        } else {
            return date('m-d H:i', $timestamp);
        }
    }
}
if (!function_exists('formatmonthday')) {
    /**
     * 日期格式处理
     * @param string|null $value
     * @return string
     */
    function formatmonthday(?string $value): string
    {
        if (empty($value)) return '';
        $value = str_replace(['年', '月', '日'], ['-', '-', ''], $value);
        return date('Y-m-d',strtotime($value));
    }
}