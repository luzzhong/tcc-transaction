<?php

declare(strict_types=1);
/**
 * This is a TCC distributed transaction component.
 * @link     https://github.com/luzzhong/tcc-transaction
 * @document https://github.com/luzzhong/tcc-transaction/blob/master/README.md
 * @license  https://github.com/luzzhong/tcc-transaction/blob/master/LICENSE
 */
namespace LoyaltyLu\TccTransaction\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RpcClient\ProxyFactory;
use Hyperf\Utils\Traits\Container;
use LoyaltyLu\TccTransaction\Annotation\Compensable;

/**
 * @Aspect
 */
class CompensableAnnotationAspect extends AbstractAspect
{
    use Container;

    public $annotations = [
        Compensable::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Compensable $annotation */
        $annotation = $metadata->method[Compensable::class] ?? null;

        $annotation->master['proxy'] = ProxyFactory::get($annotation->master['services']);

        foreach ($annotation->slave as $key => $item) {
            $annotation->slave[$key]['proxy'] = ProxyFactory::get($item['services']);
        }
        self::set($annotation->master['proxy'], $annotation);

        return $proceedingJoinPoint->process();
    }
}
