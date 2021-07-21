<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Util;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * 基于redis实现的分布式锁
 * Class Lock.
 */
class Lock
{
    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    /**
     * 锁 key 的集合.
     * @var array
     */
    private $keysLock = []; //所有已经锁定的key值

    private $cachePrefix = 'tcc:lock:'; //锁前缀

    /**
     * 加锁
     * @param $key
     * @param int $expire 秒 默认10秒
     */
    public function lock($key, int $expire = 10): bool
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
     */
    public function unLock($key): bool
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
     * 删除所有锁定的key.
     */
    public function unlockAll(): bool
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
     * 获取key.
     * @param $key
     * @return string
     */
    private function getKey($key)
    {
        return $this->cachePrefix . ':' . $key;
    }
}
