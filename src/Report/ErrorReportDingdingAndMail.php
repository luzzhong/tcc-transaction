<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use function foo\func;

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
    public function send(string $title, array $msgs)
    {
        $parallel = new Parallel();
        $parallel->add(function () use ($title, $msgs) {
            $dingding = make(ErrorReportDingding::class);
            $dingding->send($title, $msgs);
        });
        $parallel->add(function () use ($title, $msgs) {
            $mail = make(ErrorReportMail::class);
            $mail->send($title, $msgs);
        });
        try {
            $parallel->wait();
            return true;
        } catch (ParallelExecutionException $exception) {
            return false;
        }
    }
}