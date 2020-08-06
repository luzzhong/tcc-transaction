<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Util;

use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;

/**
 * 基于redis实现的分布式锁
 * Class Lock
 * @package LoyaltyLu\TccTransaction\Util
 */
class Lock
{
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;
    /**
     * 锁 key 的集合
     * @var array
     */
    private $keysLock = [];//所有已经锁定的key值

    private $cachePrefix = 'tcc:lock:';//锁前缀

    /**
     * 加锁
     * @param $key
     * @param string $prefix
     * @param null $expire 秒 默认10秒
     */
    public function lock($key, $expire = 10)
    {
        $keyUse = $this->getKey($key);
        $result = $this->redis->set($keyUse, 1, ['EX' => $expire, 'NX']);
        if ($result === false) {
            return false;
        }
        $this->keysLock[] = $key;
        return true;
    }

    /**
     * 解锁
     * @param $key
     * @param $prefix
     * @return mixed
     */
    public function unLock($key)
    {
        $keyUse = $this->getKey($key);
        $result = $this->redis->del($keyUse);
        if ($result === false) {
            return false;
        }
        $lockKey = array_search($key, $this->keysLock);
        unset($this->keysLock[$lockKey]);
        return true;
    }

    /**
     * 删除所有锁定的key
     * @return bool
     */
    public function unlockAll()
    {
        if (empty($this->keysLock)) {
            return true;
        }
        foreach ($this->keysLock as $key) {
            $keyUse = $this->getKey($key);
            $this->handler->del($keyUse);
        }
        $this->keysLock = [];
        return true;
    }

    /**
     * 获取key
     * @param $key
     * @return string
     */
    private function getKey($key)
    {
        return $this->cachePrefix . ':' . $key;
    }

}