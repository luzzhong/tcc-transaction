<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Util;


use Hyperf\Contract\ConfigInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->mailConfig = config('transaction.mailer');
    }

    public function send($title, $content)
    {
        if (!$this->checkConfig()) {
            var_dump("配置检查失败");
            return false;
        }
        var_dump("send mail");
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'];

            //Recipients
            $mail->setFrom($this->config['from']);
            foreach ($this->config['mail_to'] as $oneTarget) {
                $mail->addAddress($oneTarget);     //添加发送目标
            }

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $title;
            $mail->Body = $content;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function checkConfig()
    {
        if (!empty($this->config['host'])
            && !empty($this->config['username'])
            && !empty($this->config['password'])
            && !empty($this->config['port'])
            && !empty($this->config['from'])
            && !empty($this->config['mail_to'])
            && is_array($this->config['mail_to'])) {
            return true;
        } else {
            return false;
        }
    }
}