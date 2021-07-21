<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
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
     * 发送消息.
     * @return mixed
     */
    public function send(string $title, array $msgs)
    {
        if ($this->config['open'] === true) {
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
