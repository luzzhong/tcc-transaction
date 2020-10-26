# tcc-transaction

>>有兴趣优化和完善的小伙伴欢迎联系我 vx:ai979126035

基于Hyperf的TCC分布式事务



#### Tcc注意事项:
*   并发控制
*   允许空回滚
*   防悬挂控制
*   幂等控制

###### 目前支持钉钉以及邮件推送事务失败通知，感谢 <a href="https://github.com/lizhanfei" target='_blank'>@lizhanfei</a>

#### 使用方法：

`composer require loyaltylu/tcc-transaction`


`php bin/hyperf.php vendor:publish loyaltylu/tcc-transaction`


* 引用注解：

```php
    use LoyaltyLu\TccTransaction\Annotation\Compensable;
```

* 在需要调用分布式事务的方法上加入注解


```php
    /**
     * @Inject
     * @var CalculatorServiceInterface
     */
    private $service;

    /**
     * @Compensable(
     *     master={"services": CalculatorServiceInterface::class, "tryMethod": "creditOrderTcc", "confirmMethod": "confirmCreditOrderTcc", "cancelMethod": "cancelCreditOrderTcc"},
     *     slave={
     *         {"services": PayServiceInterface::class, "tryMethod": "creditAccountTcc", "confirmMethod": "confirmCreditAccountTcc", "cancelMethod": "cancelCreditAccountTcc"},
     *     }
     * )
     * @return array
     */

  public function index(){
        $input = $this->request->input('id');
        return $this->service->creditOrderTcc($input);
   }
  

```
* 注解说明：

    * master：主业务服务（事务发起方）

    * slave：从业务服务（事务参与方）（多个）

    * services：服务接口
    
    * tryMethod：try阶段方法
    
    * confirmMethod： confirm阶段方法
    
    * cancelMethod： cancel阶段方法




* 事务调用


    只需要调用主业务服务try阶段方法，事务管理器会根据各个服务返回决定执行confirm阶段还是cancel阶段
    
```php
     $input = $this->request->input('id');
     return $this->service->creditOrderTcc($input);
```


* 事务传参


    事务各个阶段如果需要传参只需要对主业务服务try阶段方法传入参数，事务管理器会将try阶段参数代入各个阶段方法，同时也会将try阶段返回值一并代入下个阶段


* 事务参与者应响应失败需要使用`throw`抛出


    目前不支持自定义失败异常，后期会考虑
    

```php
/**
 * Class CalculatorService.
 * @RpcService(name="PayService", protocol="jsonrpc-http", server="jsonrpc-http", publishTo="consul")
 */
class PayService implements PayServiceInterface
{
public function creditAccountTcc($input)
    {
        throw new \Exception('msg');
        
    }
}
```



#### 下面以下单为例的【正常】流程：

*   1、将TCC信息保存起来，状态为：待处理
*   2、预处理阶段（Try），并将处理成功的信息保存起来，以便后续【提交/回滚】使用。
*   3、根据预处理的结果，决定是提交还是回滚。
*   4、如果全部处理成功，则修改1中保存起来的数据的状态：处理成功；
*   5、结束处理，并返回

#### 【异常】流程说明

 此异常处理，主要是处理【提交/回滚】时失败，定时查看数据情况。 一旦发现有处理异常的数据，则再一次触发【提交/回滚】流程，如果达到重试上限（默认1次），最后仍处理失败，需要通知相关人员（发送邮件），进行人工干预
 
#### 注意事项

* 事务节点信息、事务参数、各个阶段返回值皆保存在redis需要对reids进行持久化设置，避免数据丢失
* 为防止服务异常使用了消息队列对事务进行回查，如果系统在执行中宕机或其他异常导致服务不可用则消息队列会对异常队列进行补偿，消息队列目前只支持NSQ，也需要进行持久化，避免数据丢失
* 消息对列采用延时队列，不会立即对事务进行回查，回查时间可以在配置文件中设置，设置回查时间不应小于各个服务的最大响应时间，避免出现异常



#### TODO：

* 优化消息队列模块，增加队列选择


项目正在完善中，欢迎大家提出宝贵意见和建议，您的star是我们前进的动力~
