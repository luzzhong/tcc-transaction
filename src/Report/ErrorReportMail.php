<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use LoyaltyLu\TccTransaction\Util\Mailer;

class ErrorReportMail implements ErrorReport
{
    /**
     * 发送消息
     * @param $title
     * @param $msg
     * @return mixed
     */
    public function send($title, $msg)
    {
        var_dump($title. "--". $msg);
        $mailUtil = make(Mailer::class);
        return $mailUtil->send($title, $msg);
    }
}