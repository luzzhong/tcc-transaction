<?php


namespace LoyaltyLu\TccTransaction;


use Hyperf\Nsq\Nsq;

class NsqProducer
{
    public static function sendQueue($tid, $proceedingJoinPoint,$topic='tcc-transaction')
    {
        $nsq = make(Nsq::class);
        $msg = json_encode(['tid' => $tid, 'info' => $proceedingJoinPoint, 'id' => 1]);
        /** @var $nsq Nsq **/
        return $nsq->publish($topic, $msg, config('nsq_detection_time',5));
    }
}
