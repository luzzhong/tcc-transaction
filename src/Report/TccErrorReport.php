<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use LoyaltyLu\TccTransaction\Util\Lock;

/**
 * tcc 异常报告
 * Class TccErrorReport
 * @package LoyaltyLu\TccTransaction\Report
 */
class TccErrorReport
{
    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @Inject()
     * @var Lock
     */
    private $lock;

    /**
     * 回滚异常报警
     * @param $title
     * @param $tid
     * @param $tccMethod
     * @param $status
     */
    public function cancleFailReport($title, $tid, $tccMethod, $status)
    {
        $key = "tcc:canclefail:report:" . $tid;
        if ($this->lock->lock($key, 10)) {
            $content = [];
            $content[] = "事务: {$tid}";
            $content[] = "阶段: {$tccMethod}";
            $content[] = "执行状态: {$status}";
            if ($this->container->get(ErrorReport::class)->send($title, $content)) {
                $this->lock->unLock($key);
            }
        }
    }
}