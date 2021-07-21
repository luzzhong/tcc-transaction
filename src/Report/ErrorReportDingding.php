<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Report;

use LoyaltyLu\TccTransaction\Util\Dingtalk;

/**
 * 钉钉异常报告
 * Class ErrorReportDingding.
 */
class ErrorReportDingding implements ErrorReport
{
    private $config;

    public function __construct()
    {
        $this->config = config('transaction.dingtalk');
    }

    /**
     * 发送消息.
     * @return mixed
     */
    public function send(string $title, array $msgs)
    {
        if ($this->config['open'] === true) {
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
