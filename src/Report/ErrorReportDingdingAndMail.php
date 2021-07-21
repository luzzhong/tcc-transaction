<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Report;

use Exception;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;

/**
 * 钉钉和邮件发送异常报告
 * Class ErrorReportDingdingAndMail.
 */
class ErrorReportDingdingAndMail implements ErrorReport
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * 发送消息.
     * @param string $title
     * @param array $msgs
     * @return bool
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
        } catch (ParallelExecutionException | Exception $exception) {
            $msg = '钉钉和邮件报警失败，错误信息：' . $exception->getMessage() . ' 文件：' . $exception->getFile() . "[{$exception->getLine()}]\n";
            $this->logger->error($msg);
            return false;
        }
    }
}
