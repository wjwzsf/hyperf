<?php


namespace App\Service;
/**
 * 抽象类
 * Interface UserServiceInterface
 * @package App\Service
 */
interface UserServiceInterface
{
    public function getInfoById(int $id);
}
/**
 * 简单对象注入
 * Class UserService
 * @package App\Service
 */
class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        return $id+200;
    }
}