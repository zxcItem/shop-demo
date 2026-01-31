<?php

namespace app\data\service\oauth;

/**
 * 第三方登录驱动接口
 * @class Contract
 * @package app\data\service\oauth
 */
abstract class Contract
{
    /**
     * 验证授权令牌
     * @param string $openid 用户唯一标识
     * @param string $token 授权令牌
     * @return array [openid, unionid, nickname, headimg]
     * @throws \Exception
     */
    abstract public function verify(string $openid, string $token): array;
}
