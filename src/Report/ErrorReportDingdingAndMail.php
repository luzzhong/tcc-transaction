<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Exception;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 钉钉和邮件发送异常报告
 * Class ErrorReportDingdingAndMail
 * @package LoyaltyLu\TccTransaction\Report
 */
class ErrorReportDingdingAndMail implements ErrorReport
{
    /**
     * @Inject()
     * @var StdoutLoggerInterface
     */
    protected $logger;

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
            $msg = "钉钉和邮件报警失败，错误信息：" . $exception->getMessage() . " 文件：" . $exception->getFile() . "[{$exception->getLine()}]\n";
            $this->logger->error($msg);
            return false;
        } catch (Exception $exception) {
            $msg = "钉钉和邮件报警失败，错误信息：" . $exception->getMessage() . " 文件：" . $exception->getFile() . "[{$exception->getLine()}]\n";
            $this->logger->error($msg);
            return false;
        }
    }
}