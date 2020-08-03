<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use LoyaltyLu\TccTransaction\Util\Mailer;

class ErrorReportMail implements ErrorReport
{
    /**
     * 发送消息
     * @param $title
     * @param $msgs
     * @return mixed
     */
    public function send($title, $msgs)
    {
        $content = "<h1> {$title} </h1>";
        foreach ($msgs as $msg) {
            $content .= " {$msg} <br/>";
        }
        var_dump($title. "--". $content);
        $mailUtil = make(Mailer::class);
        return $mailUtil->sendHtml($title, $content);
    }
}