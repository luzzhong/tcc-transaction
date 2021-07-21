<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;

class State
{
    public const RETRIED_CANCEL_COUNT = 0;

    public const RETRIED_CONFIRM_COUNT = 0;

    public const RETRIED_MAX_COUNT = 1;

    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    public function initStatus($services, $params)
    {
        $container = ApplicationContext::getContainer();
        $generator = $container->get(IdGeneratorInterface::class);
        $tid = (string) $generator->generate();
        $tccData = [
            'tid' => $tid,
            'services' => $services,
            'content' => $params,
            'status' => 'normal',
            'tcc_method' => 'tryMethod',
            'retried_cancel_count' => self::RETRIED_CANCEL_COUNT,
            'retried_confirm_count' => self::RETRIED_CONFIRM_COUNT,
            'retried_cancel_nsq_count' => self::RETRIED_CANCEL_COUNT,
            'retried_confirm_nsq_count' => self::RETRIED_CONFIRM_COUNT,
            'retried_max_count' => config('transaction.retried_max_count', self::RETRIED_MAX_COUNT),
            'create_time' => time(),
            'last_update_time' => time(),
        ];
        $this->redis->hSet('Tcc', $tid, Json::encode($tccData));
        return $tid;
    }

    public function upAllTccStatus($tid, $tcc_method, $status, $params)
    {
        $originalData = $this->redis->hget('Tcc', $tid);
        $originalData = Json::decode($originalData);
        $originalData['tcc_method'] = $tcc_method;
        $originalData['status'] = $status;
        $originalData['last_update_time'] = time();
        $originalData['content'] = $params;
        $this->redis->hSet('Tcc', $tid, Json::encode($originalData));
    }

    public function upTccStatus($tid, $tcc_method, $key)
    {
        $originalData = $this->redis->hget('Tcc', $tid);
        $originalData = Json::decode($originalData);
        if ($originalData[$key] >= self::RETRIED_MAX_COUNT) {
            $originalData['status'] = 'fail';
            $this->redis->hSet('Tcc', $tid, Json::encode($originalData));
            return false;
        }
        ++$originalData[$key];
        $originalData['tcc_method'] = $tcc_method;
        $originalData['status'] = 'abnormal';
        $originalData['last_update_time'] = time();
        $this->redis->hSet('Tcc', $tid, Json::encode($originalData));

        return true;
    }
}
