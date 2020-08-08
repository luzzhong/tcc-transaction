<?php


namespace LoyaltyLu\TccTransaction;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use LoyaltyLu\TccTransaction\Report\TccErrorReport;
use Psr\Container\ContainerInterface;

class TccTransaction
{
    /**
     * @Inject()
     * @var State
     */
    protected $state;
    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    public function send($proceedingJoinPoint, $servers, $tcc_method, $tid, $params, $flag = 0)
    {
        if ($flag) {
            NsqProducer::sendQueue($tid, $proceedingJoinPoint, 'tcc-transaction');
        }
        $this->state->upAllTccStatus($tid, $tcc_method, 'normal', $params);
        $parallel = new Parallel();
        if ($tcc_method == 'tryMethod') {
            $parallel->add(function () use ($proceedingJoinPoint) {
                return $proceedingJoinPoint->process();
            });
        } else {
            $parallel->add(function () use ($params, $servers, $tcc_method) {
                $container = ApplicationContext::getContainer()->get($servers->master['services']);
                $tryMethod = $servers->master[$tcc_method];
                return $container->$tryMethod($params);
            });
        }

        foreach ($servers->slave as $key => $value) {
            $parallel->add(function () use ($value, $params, $tcc_method, $key) {
                $container = ApplicationContext::getContainer()->get($value['services']);
                $tryMethod = $value[$tcc_method];
                return $container->$tryMethod($params);
            });
        }
        try {
            $results = $parallel->wait();
            $params[$tcc_method] = $results;
            $this->state->upAllTccStatus($tid, $tcc_method, 'success', $params);
            if ($tcc_method == 'tryMethod') {
                $results = $this->send($proceedingJoinPoint, $servers, 'confirmMethod', $tid, $params);
            }
            return $results;
        } catch (ParallelExecutionException $exception) {
            $this->state->upAllTccStatus($tid, $tcc_method, 'fail', $params);
            return $this->errorTransction($tcc_method, $proceedingJoinPoint, $servers, $tid, $params);
        }

    }

    public function errorTransction($tcc_method, $proceedingJoinPoint, $servers, $tid, $params)
    {
        switch ($tcc_method) {
            case 'tryMethod':
                return $this->send($proceedingJoinPoint, $servers, 'cancelMethod', $tid, $params);
            case 'cancelMethod':
                if ($this->state->upTccStatus($tid, $tcc_method, 'retried_cancel_count')) {
                    return $this->send($proceedingJoinPoint, $servers, 'cancelMethod', $tid, $params);
                }
                //异常通知
                make(TccErrorReport::class)->cancleFailReport("事务异常: 回滚失败", $tid, 'cancelMethod', 'fail');
                return ['status' => 0, 'msg' => '回滚失败'];
            case 'confirmMethod':
                if ($this->state->upTccStatus($tid, $tcc_method, 'retried_confirm_count')) {
                    return $this->send($proceedingJoinPoint, $servers, 'confirmMethod', $tid, $params);
                }
                return $this->send($proceedingJoinPoint, $servers, 'cancelMethod', $tid, $params);
        }
    }
}
