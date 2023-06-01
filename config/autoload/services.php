<?php

return [
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],
    'consumers' => [],
    'providers' => [],
    'drivers' => [
        'nacos' => [
            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
            // 'url' => '',
            // The nacos host info
            'host' => env('NACOS_HOST'),//ip地址
            'port' => env('NACOS_PORT'),//端口号，之前只写8848不行 还是要加上后边儿的才可以，相当于到登录页面
            // The nacos account info
            'username' => env('NACOS_NAME'),//账号
            'password' => env('NACOS_PASSWORD'),//密码
            'group_name' => env('NACOS_GROUPNAME'),//分组名称 一般大家都写这个
            'namespace_id' => env('NACOS_NAMESPACE_ID'),//命名空间 可选 (public/dev/test/pro)
            'heartbeat' => 5,//心跳 五秒一次
            'ephemeral' => true //是否注册临时实例
        ],
    ],
];