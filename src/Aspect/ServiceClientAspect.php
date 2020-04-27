<?php


namespace LoyaltyLu\TccTransaction\Aspect;


use App\Controller\IndexController;
use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Nsq\Nsq;
use Hyperf\RpcClient\Exception\RequestException;
use Hyperf\RpcClient\ServiceClient;
use Hyperf\Di\Container;
use LoyaltyLu\TccTransaction\NsqProducer;
use LoyaltyLu\TccTransaction\State;
use LoyaltyLu\TccTransaction\TccTransaction;

/**
 * @Aspect()
 * Class ServiceClientAspect
 * @package Hyperf\TccTransaction\Aspect
 */
class ServiceClientAspect extends AbstractAspect
{


    public $classes = [
        ServiceClient::class . "::__call",
    ];

    /**
     * @Inject()
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
        $result = self::guessBelongsToRelation();
        $servers = CompensableAnnotationAspect::get($result['class']);
        if ($servers && count($servers->slave) > 0) {
            $tcc_method = array_search($result['function'], $servers->master);
            if ($tcc_method == 'tryMethod') {
                $params = $proceedingJoinPoint->getArguments()[1][0];
                $tid = $this->state->initStatus($servers, $params);
                NsqProducer::sendQueue($tid,$proceedingJoinPoint,'tcc-transaction');
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
