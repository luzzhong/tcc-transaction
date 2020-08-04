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
    private $config;

    public function __construct()
    {
        $this->config = config('transaction.dingtalk');
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
            //组装数据
            $content = "## {$title} \n";
            foreach ($msgs as $msg) {
                $content .= "### {$msg} \n";
            }
            $dingUtil = make(Dingtalk::class);
            $dingUtil->setHookUrl($this->config['access_token']);
            return $dingUtil->sendMarkDown($title, $content);
        }
        return false;
    }
}