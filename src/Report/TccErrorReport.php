<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Report;

use Hyperf\Di\Annotation\Inject;
use LoyaltyLu\TccTransaction\Util\Lock;
use Psr\Container\ContainerInterface;

/**
 * tcc 异常报告
 * Class TccErrorReport.
 */
class TccErrorReport
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var Lock
     */
    protected $lock;

    /**
     * 回滚异常报警.
     * @param $title
     * @param $tid
     * @param $tccMethod
     * @param $status
     */
    public function cancleFailReport($title, $tid, $tccMethod, $status)
    {
        $key = 'tcc:canclefail:report:' . $tid;
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
