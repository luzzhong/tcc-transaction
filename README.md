# tcc-transaction
基于Hyperf的分布式事务

#### 使用方法：

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
