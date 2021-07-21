<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Util;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件发送工具类.
 *
 * Class Mailer
 */
class Mailer
{
    private $config;

    /**
     * 设置配置信息.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * 发送html内容的邮件.
     * @param $title
     * @param $content  // 邮件内容，支持html
     */
    public function sendHtml($title, $content): bool
    {
        if (! $this->checkConfig()) {
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->config['port'];

            $mail->setFrom($this->config['from']);
            foreach ($this->config['mail_to'] as $oneTarget) {
                $mail->addAddress($oneTarget);     //添加发送目标
            }

            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $title;
            $mail->Body = $content;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 检查配置是否完整.
     */
    private function checkConfig(): bool
    {
        return ! empty($this->config['host'])
            && ! empty($this->config['username'])
            && ! empty($this->config['password'])
            && ! empty($this->config['port'])
            && ! empty($this->config['from'])
            && ! empty($this->config['mail_to'])
            && is_array($this->config['mail_to']);
    }
}
