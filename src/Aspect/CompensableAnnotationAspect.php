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

namespace LoyaltyLu\TccTransaction\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RpcClient\ProxyFactory;
use Hyperf\RpcClient\ServiceClient;
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
