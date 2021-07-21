<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Report;

/**
 * 异常报告
 * Interface ErrorReport.
 */
interface ErrorReport
{
    /**
     * 发送消息.
     * @return mixed
     */
    public function send(string $title, array $msgs);
}
