<?php


namespace App\Controller;

use Firebase\JWT\JWT;//引入jwt
use Hyperf\HttpServer\Annotation\AutoController;//自动路由

#[AutoController]
class UserController extends AbstractController
{

    public function login()
    {
        $user = $this->request->input('user');
        $password = $this->request->input('password');
        // 验证用户信息，假设用户ID为1
        $user_id = 7293;

        $payload = [
            'user_id' => $user_id,
            'exp' => time() + config('jwt.exp'),
        ];
        $token = JWT::encode($payload, config('jwt.secret'), config('jwt.algorithm'));
        return ['token' => $token];
    }
    public function profile()
    {
//        可以使用 $request->getAttribute('user_id') 方法从请求对象中获取已存储的属性值，例如：
//
//php
//$user_id = $request->getAttribute('user_id');
//请注意，在此示例中，我们将 user_id 属性存储在请求对象的属性列表中，以便后续处理。如果您要存储其他类型的信息，也可以使用类似的方法。但是，请确保只存储必要的信息，并遵循最小授权原则，确保安全性和隐私性。

        $user_id = $this->request->getAttribute('user_id');
        // 根据用户ID获取用户信息
        $user = [
            'id' => $user_id,
            'name' => 'test',
            'email' => 'test@example.com',
        ];
        return $user;
    }
}