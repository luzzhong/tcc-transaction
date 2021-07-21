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
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RpcClient\ServiceClient;
use LoyaltyLu\TccTransaction\NsqProducer;
use LoyaltyLu\TccTransaction\State;
use LoyaltyLu\TccTransaction\TccTransaction;

/**
 * @Aspect
 * Class ServiceClientAspect
 */
class ServiceClientAspect extends AbstractAspect
{
    public $classes = [
        ServiceClient::class . '::__call',
    ];

    /**
     * @Inject
     * @var State
     */
    protected $state;

    /**
     * @var TccTransaction
     */
    private $tccTransaction;

    public function __construct()
    {
        $this->tccTransaction = make(TccTransaction::class);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $result = $this->guessBelongsToRelation();
        $servers = CompensableAnnotationAspect::get($result['class']);
        if ($servers && count($servers->slave) > 0) {
            $tcc_method = array_search($result['function'], $servers->master);
            if ($tcc_method === 'tryMethod') {
                $params = $proceedingJoinPoint->getArguments()[1][0];
                $tid = $this->state->initStatus($servers, $params);
                NsqProducer::sendQueue($tid, $proceedingJoinPoint, 'tcc-transaction');
                return $this->tccTransaction->send($proceedingJoinPoint, $servers, $tcc_method, $tid, $params);
            }
        }
        return $proceedingJoinPoint->process();
    }

    protected function guessBelongsToRelation()
    {
        [$one, $two, $three, $four, $five, $six, $seven, $eight, $nine] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 9);
        return $eight;
    }
}
