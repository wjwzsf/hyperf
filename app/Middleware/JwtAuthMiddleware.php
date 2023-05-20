<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Hyperf\Config\Config;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取 JWT 令牌
        $token = $request->getHeaderLine('Authorization');

        if (!empty($token) && preg_match('/^Bearer\s+(.*)$/', $token, $matches)) {
            $jwtToken = $matches[1];
            $secret = config('app.jwt_secret');
            try {
                // 验证 JWT 是否合法
                $decoded = JWT::decode($jwtToken, $secret, ['HS256']);

                // 将解码后的信息存储到请求对象中，以便后续处理
                $request = $request->withAttribute('user_id', $decoded->user_id);

                return $handler->handle($request);
            } catch (\Exception $e) {
                // JWT 验证失败，返回未授权响应
                $response = new \Hyperf\HttpMessage\Server\Response();
                $response = $response->withStatus(401)->withBody(new SwooleStream('Unauthorized'));
                return $response;
            }
        }

        // JWT 令牌不存在或格式不正确，返回未授权响应
        $response = new \Hyperf\HttpMessage\Server\Response();
        $response = $response->withStatus(401)->withBody(new SwooleStream('Unauthorized'));
        return $response;
    }
}