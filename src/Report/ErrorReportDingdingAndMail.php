<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use LoyaltyLu\TccTransaction\Util\Dingtalk;
use LoyaltyLu\TccTransaction\Util\Mailer;

/**
 * 钉钉和邮件发送异常报告
 * Class ErrorReportDingdingAndMail
 * @package LoyaltyLu\TccTransaction\Report
 */
class ErrorReportDingdingAndMail implements ErrorReport
{
    /**
     * 发送消息
     * @param $title
     * @param $msgs
     * @return mixed
     */
    public function send($title, $msgs)
    {
        /**
         * 钉钉组装数据，发送
         */
        $content = "## {$title} \n";
        foreach ($msgs as $msg) {
            $content .= "### {$msg} \n";
        }
        $dingUtil = make(Dingtalk::class);
        $dingUtil->sendMarkDown($title, $content);
        /**
         * 邮件组装数据，发送
         */
        $content = "<h1> {$title} </h1>";
        foreach ($msgs as $msg) {
            $content .= " {$msg} <br/>";
        }
        var_dump($title. "--". $content);
        $mailUtil = make(Mailer::class);
        $mailUtil->sendHtml($title, $content);

        return true;
    }
}