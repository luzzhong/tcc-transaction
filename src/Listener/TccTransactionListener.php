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
 *     nums=2,
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
        $tccInfo = $this->redis->hget("Tcc", $info->tid);
        $data = json_decode($tccInfo, true);
        if ($data['last_update_time'] + 5 > time()) {
            $nsq = make(Nsq::class);
            $msg = json_encode(['tid' => $info->tid, 'info' => $info->info]);
            $nsq->publish("tcc-transaction", $msg, 5);
            return Result::ACK;
        }
        if ($data['status'] != 'success') {
            if ($data['tcc_method'] == 'tryMethod') {
                $this->tcc->send($info->info, (object)$data['services'], 'cancelMethod', $info->tid, $data['content'], 1);
            } elseif ($data['tcc_method'] == 'cancelMethod') {
                if ($this->state->upTccStatus($info->tid, 'cancelMethod', 'retried_cancel_nsq_count')) {#尝试提交次数
                    $this->tcc->send($info->info, (object)$data['services'], 'cancelMethod', $info->tid, $data['content'], 1);
                } else {
                    #TODO:: 通知措施
                    $this->redis->hDel('Tcc', $info->tid);
                    $this->redis->hSet('TccError', $info->tid, $tccInfo);
                }
            } elseif ($data['tcc_method'] == 'confirmMethod') {
                if ($this->state->upTccStatus($info->tid, 'confirmMethod', 'retried_confirm_nsq_count')) {#尝试提交次数
                    $this->tcc->send($info->info, (object)$data['services'], 'confirmMethod', $info->tid, $data['content'], 1);
                }
                $this->tcc->send($info->info, (object)$data['services'], 'cancelMethod', $info->tid, $data['content'], 1);#尝试confirm失败就要cancel
            }
        } elseif ($data['status'] == 'success' && $data['tcc_method'] == 'cancelMethod') {#正常删除
            $this->redis->hDel('Tcc', $info->tid);
            $this->redis->hSet('TccSuccess', $info->tid, $tccInfo);
        } elseif ($data['status'] == 'success' && $data['tcc_method'] == 'confirmMethod') { #正常删除
            $this->redis->hDel('Tcc', $info->tid);
            $this->redis->hSet('TccSuccess', $info->tid, $tccInfo);
        }
        return Result::ACK;
    }
}
