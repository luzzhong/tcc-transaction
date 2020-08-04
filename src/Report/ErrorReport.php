<?php

declare(strict_types=1);

namespace LoyaltyLu\TccTransaction\Report;

/**
 * 异常报告
 * Interface ErrorReport
 * @package LoyaltyLu\TccTransaction\Report
 */
interface ErrorReport
{
    /**
     * 发送消息
     * @param $title
     * @param $msgs
     * @return mixed
     */
    public function send(string $title, array $msgs);
}