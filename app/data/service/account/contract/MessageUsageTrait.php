<?php


namespace app\data\service\account\contract;

use app\data\model\account\DataAccountMsms;
use think\admin\Exception;

/**
 * 短信通用接口
 * @class MessageUsageTrait
 * @package app\data\service\contract
 */
trait MessageUsageTrait
{
    /**
     * 业务场景
     * @var string[]
     */
    protected $scenes = [];

    /**
     * 获取短信区域配置
     * @return array[]
     */
    public static function regions(): array
    {
        return static::$regions ?? [];
    }

    /**
     * 根据场景配置发送验证码
     * @param string $scene 业务场景
     * @param string $phone 手机号码
     * @param array $params 模板变量
     * @param array $options 其他配置
     * @return array
     * @throws \think\admin\Exception
     */
    public function verify(string $scene, string $phone, array $params = [], array $options = []): array
    {
        $scenes = array_change_key_case($this->scenes);
        if (empty($scenes) || empty($scenes[strtolower($scene)])) {
            throw new Exception('业务场景未配置！');
        }
        $result = $this->send($scenes[strtolower($scene)], $phone, $params, $options);
        DataAccountMsms::mk()->save([
            'unid'   => intval(sysvar('Data_account_user_unid')),
            'usid'   => intval(sysvar('Data_account_user_usid')),
            'type'   => class_basename(static::class),
            'smsid'  => $result['smsid'] ?? '',
            'scene'  => $scene,
            'phone'  => $phone,
            'status' => 1,
            'result' => json_encode($result['result'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'params' => json_encode($result['params'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
        return $result;
    }
}