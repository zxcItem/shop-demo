<?php


namespace app\data\service\contract;

/**
 * 通用短信接口类
 * @class MessageInterface
 * @package app\data\service\contract
 */
interface MessageInterface
{
    /**
     * 初始化短信通道
     * @return static
     * @throws \think\admin\Exception
     */
    public function init(array $config = []): MessageInterface;

    /**
     * 发送短信内容
     * @param string $code 短信模板CODE
     * @param string $phone 接收手机号码
     * @param array $params 短信模板变量
     * @param array $options 其他配置参数
     * @return array
     * @throws \think\admin\Exception
     */
    public function send(string $code, string $phone, array $params = [], array $options = []): array;

    /**
     * 发送短信验证码
     * @param string $scene 业务场景
     * @param string $phone 手机号码
     * @param array $params 模板变量
     * @param array $options 其他配置
     * @return array
     */
    public function verify(string $scene, string $phone, array $params = [], array $options = []): array;

    /**
     * 获取区域配置
     * @return array
     */
    public static function regions(): array;
}