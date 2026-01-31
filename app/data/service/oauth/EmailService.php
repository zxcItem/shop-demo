<?php

namespace app\data\service\oauth;

use PHPMailer\PHPMailer\PHPMailer;
use think\admin\Library;

/**
 * 邮箱登录服务
 * @class EmailService
 * @package app\data\service\oauth
 */
class EmailService extends Contract
{
    /**
     * 发送验证码
     * @param string $email 邮箱地址
     * @return bool
     * @throws \Exception
     */
    public function sendCode(string $email): bool
    {
        // 检查缓存
        $cacheKey = 'email_verify_' . md5($email);
        $cacheData = Library::$sapp->cache->get($cacheKey);
        if (!empty($cacheData) && isset($cacheData['time']) && $cacheData['time'] > time() - 60) {
            throw new \Exception('验证码发送太频繁，请稍后再试');
        }

        // 生成验证码
        $code = rand(100000, 999999);

        // 发送邮件
        $this->sendEmail($email, $code);

        // 写入缓存 (有效期 10 分钟)
        Library::$sapp->cache->set($cacheKey, ['code' => $code, 'time' => time()], 600);

        return true;
    }

    /**
     * 验证授权令牌 (邮箱验证码)
     * @param string $openid 邮箱地址
     * @param string $token 验证码
     * @return array [openid, unionid, nickname, headimg]
     * @throws \Exception
     */
    public function verify(string $openid, string $token): array
    {
        $email = $openid;
        $code = $token;

        $cacheKey = 'email_verify_' . md5($email);
        $cacheData = Library::$sapp->cache->get($cacheKey);

        if (empty($cacheData) || !isset($cacheData['code']) || $cacheData['code'] != $code) {
            throw new \Exception('验证码错误或已失效');
        }

        // 验证成功，清除缓存
        Library::$sapp->cache->delete($cacheKey);

        // 邮箱登录默认使用邮箱前缀作为昵称
        $username = strstr($email, '@', true);

        return [
            'openid'   => $email,
            'unionid'  => '',
            'nickname' => $username,
            'headimg'  => '',
        ];
    }

    /**
     * 发送邮件内部方法
     * @param string $to 收件人邮箱
     * @param string|int $code 验证码
     * @throws \Exception
     */
    private function sendEmail($to, $code)
    {
        $mail = new PHPMailer(true);

        try {
            // 配置
            $mail->isSMTP();
            $mail->Host       = sysconf('email_smtp_host') ?: env('EMAIL_SMTP_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = sysconf('email_smtp_user') ?: env('EMAIL_SMTP_USER');
            $mail->Password   = sysconf('email_smtp_pass') ?: env('EMAIL_SMTP_PASS');
            $mail->SMTPSecure = sysconf('email_smtp_secure') ?: env('EMAIL_SMTP_SECURE') ?: 'ssl';
            $mail->Port       = sysconf('email_smtp_port') ?: env('EMAIL_SMTP_PORT') ?: 465;
            $mail->CharSet    = 'UTF-8';

            if (empty($mail->Host) || empty($mail->Username)) {
                throw new \Exception('系统未配置邮件发送服务');
            }

            // 发件人
            $fromAddr = sysconf('email_from_addr') ?: env('EMAIL_FROM_ADDR') ?: $mail->Username;
            $fromName = sysconf('email_from_name') ?: env('EMAIL_FROM_NAME') ?: 'System';
            $mail->setFrom($fromAddr, $fromName);

            // 收件人
            $mail->addAddress($to);

            // 内容
            $mail->isHTML(true);
            $mail->Subject = '登录验证码';
            $mail->Body    = "
                <div style='padding: 20px; background-color: #f5f5f5;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                        <h2 style='color: #333; margin-top: 0;'>登录验证</h2>
                        <p style='color: #666; font-size: 14px;'>您好，</p>
                        <p style='color: #666; font-size: 14px;'>您正在使用邮箱登录，本次验证码为：</p>
                        <p style='font-size: 28px; color: #009688; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>{$code}</p>
                        <p style='color: #999; font-size: 12px;'>验证码有效期为 10 分钟，请勿泄露给他人。</p>
                        <div style='border-top: 1px solid #eee; margin-top: 30px; padding-top: 10px; color: #ccc; font-size: 12px;'>
                            <p>此邮件由系统自动发送，请勿回复。</p>
                        </div>
                    </div>
                </div>
            ";
            $mail->AltBody = "您的登录验证码是：{$code}，有效期10分钟。";

            $mail->send();
        } catch (\Exception $e) {
            throw new \Exception("邮件发送失败: " . $mail->ErrorInfo);
        }
    }
}
