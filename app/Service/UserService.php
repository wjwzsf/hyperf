<?php
namespace App\Service;
//实现类
class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // 我们假设存在一个 Info 实体
        return $id+100;
    }
}
