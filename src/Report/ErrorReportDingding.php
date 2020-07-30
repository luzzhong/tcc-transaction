<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;


use LoyaltyLu\TccTransaction\Util\Dingtalk;

/**
 * 异常报告
 * Class ErrorReportDingding
 * @package LoyaltyLu\TccTransaction\Report
 */
class ErrorReportDingding implements ErrorReport
{
    /**
     * 发送消息
     * @param $title
     * @param $msg
     * @return mixed
     */
    public function send($title, $msg)
    {
        $dingUtil = make(Dingtalk::class);
        return $dingUtil->sendMarkDown($title, $msg);
    }
}