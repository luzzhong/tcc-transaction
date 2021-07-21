<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction;

use Hyperf\Nsq\Nsq;
use Hyperf\Utils\Codec\Json;

class NsqProducer
{
    /**
     * @param mixed $tid
     * @param mixed $proceedingJoinPoint
     * @param mixed $topic
     * @throws \Throwable
     */
    public static function sendQueue($tid, $proceedingJoinPoint, $topic = 'tcc-transaction')
    {
        $nsq = make(Nsq::class);
        $msg = Json::encode(['tid' => $tid, 'info' => $proceedingJoinPoint, 'id' => 1]);
        /* @var $nsq Nsq */
        return $nsq->publish($topic, $msg, config('transaction.nsq_detection_time', 5));
    }
}
