<?php
return [
    'jwt' => [
        'secret' => getenv('JWT_SECRET', 'your-random-secret'),
        // 其他 JWT 配置参数
    ],
];