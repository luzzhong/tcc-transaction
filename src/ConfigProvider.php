<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
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
                \LoyaltyLu\TccTransaction\Report\ErrorReport::class => \LoyaltyLu\TccTransaction\Report\ErrorReportDingdingAndMail::class,
            ],
        ];
    }
}
