<?php


declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Logger\Logger;
use Hyperf\Nsq\AbstractConsumer;
use Hyperf\Nsq\Annotation\Consumer;
use Hyperf\Nsq\Message;
use Hyperf\Nsq\Nsq;
use Hyperf\Nsq\Result;
use Hyperf\Redis\Redis;
use LoyaltyLu\TccTransaction\State;
use LoyaltyLu\TccTransaction\TccTransaction;

/**
 * @Consumer(
 *     topic="tcc-transaction",
 *     channel="tcc-transaction",
 *     name ="tcc-transaction",
 *     nums=1,
 *     pool="default"
 * )
 */
class TccTransactionListener extends AbstractConsumer
{
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var State
     */
    private $state;

    /**
     * @Inject()
     * @var TccTransaction
     */
    private $tcc;

    public function consume(Message $payload): ?string
    {
        $info = json_decode($payload->getBody());
        $tid = $info->tid;
        $data = json_decode($this->redis->hget("Tcc", $tid), true);
        if ($data['status'] != 'success' && $data['tcc_method'] != 'confirmMethod') {
            if ($data['tcc_method'] == 'tryMethod') {
                $this->tcc->send($info->info, (object)$data['services'], 'cancelMethod', $tid, $data['content'], 1);
            } elseif ($data['tcc_method'] == 'cancelMethod') {
                if ($this->state->upTccStatus($tid, 'cancelMethod', 'retried_cancel_count')) {#尝试提交次数
                    $this->tcc->send($info->info, (object)$data['services'], 'cancelMethod', $tid, $data['content'], 1);
                } else {
                    var_dump("cancel异常删除");
                    $this->redis->hDel('Tcc', $tid);
                }
            } elseif ($data['tcc_method'] == 'confirmMethod') {
                if ($this->state->upTccStatus($tid, 'confirmMethod', 'retried_confirm_count')) {#尝试提交次数
                    $this->tcc->send($info->info, (object)$data['services'], 'confirmMethod', $tid, $data['content'], 1);
                }
                var_dump("confirmMethod回滚或者提交");
                $params['cancel_confirm_flag'] = 1;
            }
        } elseif ($data['status'] == 'success' && $data['tcc_method'] == 'cancelMethod') {
            var_dump("cancel成功正常删除");
            $this->redis->hDel('Tcc', $tid);
        } elseif ($data['status'] == 'success' && $data['tcc_method'] == 'confirmMethod') {
            var_dump("confirmMethod成功正常删除");
            $this->redis->hDel('Tcc', $tid);
        }
        return Result::ACK;;
    }
}
