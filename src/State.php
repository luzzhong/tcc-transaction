<?php


namespace LoyaltyLu\TccTransaction;


use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

class State
{

    const RETRIED_CANCEL_COUNT = 0;#重试次数
    const RETRIED_CONFIRM_COUNT = 0;#重试次数
    const RETRIED_MAX_COUNT = 1;#最大允许重试次数

    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    /**
     * 初始化事务状态，服务列表以及参数
     * @param $services
     * @param $params
     * @return string
     */
    public function initStatus($services, $params)
    {
        $tid = session_create_id(md5(microtime()));
        $tccData = [
            'tid' => $tid, //事务id
            'services' => $services, //参与者信息
            'content' => $params, //传递的参数
            'status' => 'normal', //(normal,abnormal,success,fail)事务整体状态
            'tcc_method' => 'tryMethod', //try,confirm,cancel (当前是哪个阶段)
            'retried_cancel_count' => self::RETRIED_CANCEL_COUNT, //重试次数
            'retried_confirm_count' => self::RETRIED_CONFIRM_COUNT, //重试次数
            'retried_max_count' => self::RETRIED_MAX_COUNT, //最大允许重试次数
            'create_time' => time(), //创建时间
            'last_update_time' => time(), //最后的更新时间
        ];
        $this->redis->hSet("Tcc", $tid, json_encode($tccData));
        return $tid;
    }

    /**
     * 修改事务整体服务的状态
     * @param $tid
     * @param $data
     */
    public function upAllTccStatus($tid, $tcc_method, $status)
    {
        $originalData = $this->redis->hget("Tcc", $tid);
        $originalData = json_decode($originalData, true);
        $originalData['tcc_method'] = $tcc_method;
        $originalData['status'] = $status;
        $originalData['last_update_time'] = time();
        $this->redis->hSet('Tcc', $tid, json_encode($originalData)); //主服务状态
    }

    /**
     * 修改当前事务某个阶段重试次数
     * @param $tid
     * @param $tcc_method
     * @param $key
     * @return bool
     */
    public function upTccStatus($tid, $tcc_method, $key)
    {
        $originalData = $this->redis->hget("Tcc", $tid);
        $originalData = json_decode($originalData, true);
        if ($originalData[$key] >= self::RETRIED_MAX_COUNT) {
            $originalData['status'] = 'fail';
            $this->redis->hSet('Tcc', $tid, json_encode($originalData));
            return false;
        }
        $originalData[$key] ++;
        $originalData['tcc_method'] = $tcc_method;
        $originalData['status'] = 'abnormal';
        $originalData['last_update_time'] = time();
        $this->redis->hSet('Tcc', $tid, json_encode($originalData));

        return true;
    }
}
