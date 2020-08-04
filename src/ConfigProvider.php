<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace LoyaltyLu\TccTransaction;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for transaction.',
                    'source' => __DIR__ . '/../publish/transaction.php',
                    'destination' => BASE_PATH . '/config/autoload/transaction.php',
                ],
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'dependencies' => [
                \LoyaltyLu\TccTransaction\Report\ErrorReport::class => \LoyaltyLu\TccTransaction\Report\ErrorReportDingdingAndMail::class
            ],
        ];
    }
}
