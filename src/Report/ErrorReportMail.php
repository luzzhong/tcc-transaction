<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use LoyaltyLu\TccTransaction\Util\Mailer;

class ErrorReportMail implements ErrorReport
{
    private $config;

    public function __construct()
    {
        $this->config = config('transaction.mailer');
    }

    /**
     * 发送消息
     * @param $title
     * @param $msgs
     * @return mixed
     */
    public function send(string $title, array $msgs)
    {
        if (true === $this->config['open']) {
            $content = "<h1> {$title} </h1>";
            foreach ($msgs as $msg) {
                $content .= " {$msg} <br/>";
            }
            $mailUtil = make(Mailer::class);
            $mailUtil->setConfig($this->config);
            return $mailUtil->sendHtml($title, $content);
        }
        return false;
    }
}