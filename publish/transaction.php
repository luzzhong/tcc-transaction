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
        'access_token' => ''//钉钉群机器人hook地址
    ],
    'mailer' => [
        'host' => '',//邮箱主机
        'username' => '',//邮箱用户名
        'password' => '', //邮箱密码
        'port'=>'',//邮箱主机端口

        'from' => '',//邮件来源声明
        'from_sitetitle' => '',//来源网站title
        'mail_to' => [
            ''
        ],//接收邮件地址
    ]
];
