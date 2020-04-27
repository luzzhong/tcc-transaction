# tcc-transaction
基于Hyperf的分布式事务,开始事务后如果出现异常默认会重试1次，默认事务超时时间5s(超时时间要大于consumers配置wait_timeout)，超过5s后事务将被回收，执行补偿机制，补偿机制会默认重试1次上次执行异常服务，需要各个服务提供者的方法处理好幂等

Tcc注意事项:
*   并发控制
*   允许空回滚
*   防悬挂控制
*   幂等控制


TODO：

实现事务回滚失败通知开发者（通知方式待定）

优化各个方法，减少冗余

提取参数，发布到配置文件

#### 所需要的服务：
[nsq](https://nsq.io/overview/quick_start.html)

>   不同服务尽量配置不同nsq，避免数据混淆消费失败

`composer require hyperf/nsq`


`php bin/hyperf.php vendor:publish hyperf/nsq`


#### 使用方法：

组件尚在完善中，感兴趣的小伙伴可以先使用本地加载

`git clone https://github.com/LoyaltyLu/tcc-transaction.git`


composer 中加入
```
"require": {
    "loyaltylu/tcc-transaction":"dev-master",
 },
 
```
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

    * master：主业务服务（单个）

    * slave：从业务服务（多个）

    * services：服务接口
    
    * tryMethod：try阶段方法
    
    * confirmMethod： confirm阶段方法
    
    * cancelMethod： cancel阶段方法



项目正在完善中，欢迎大家提出宝贵意见和建议
