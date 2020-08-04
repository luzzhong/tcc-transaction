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
return [
    'retried_max_count' => 1,//最多次数
    'nsq_detection_time' => 5,//nsql检测补偿事务时间
    'dingtalk' => [
        'open' => false,//是否开启钉钉报警
        'access_token' => 'https://oapi.dingtalk.com/robot/send?access_token=***********************'//钉钉群机器人hook地址，仅支持一个
    ],
    'mailer' => [
        'open' => false,//是否开启邮箱报警
        'host' => 'smtp.**.com',//邮箱主机
        'username' => '*******@163.com',//邮箱用户名
        'password' => '******', //邮箱密码
        'port' => '465',//邮箱主机端口
        'from' => '***@163.com',//邮件来源地址声明
        'mail_to' => [
            '*****@163.com'
        ],//接收邮件地址，支持多个地址
    ]
];
