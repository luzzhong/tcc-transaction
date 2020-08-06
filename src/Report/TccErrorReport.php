<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

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
     * @var Redis
     */
    private $redis;

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
        if (!$this->redis->get($key)) {
            $content = [];
            $content[] = "事务: {$tid}";
            $content[] = "阶段: {$tccMethod}";
            $content[] = "执行状态: {$status}";
            if ($this->container->get(ErrorReport::class)->send($title, $content)) {
                $this->redis->set($key, 1, 300);
            }
        }
    }
}