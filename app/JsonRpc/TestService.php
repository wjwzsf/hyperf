<?php


namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "HyperfTest",protocol: "jsonrpc-http",server: "jsonrpc-http",publishTo: "nacos")]
class TestService implements TestServiceInterface
{
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }

    public function diff(int $a, int $b): int
    {
        return $a - $b;
    }
}