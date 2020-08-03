<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;


use LoyaltyLu\TccTransaction\Util\Dingtalk;

/**
 * 钉钉异常报告
 * Class ErrorReportDingding
 * @package LoyaltyLu\TccTransaction\Report
 */
class ErrorReportDingding implements ErrorReport
{
    /**
     * 发送消息
     * @param $title
     * @param $msgs
     * @return mixed
     */
    public function send($title, $msgs)
    {
        //组装数据
        $content = "## {$title} \n";
        foreach ($msgs as $msg) {
            $content .= "### {$msg} \n";
        }
        $dingUtil = make(Dingtalk::class);
        return $dingUtil->sendMarkDown($title, $content);
    }
}