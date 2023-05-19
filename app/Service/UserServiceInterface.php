<?php
namespace App\Service;
//抽象类
interface UserServiceInterface
{
    public function getInfoById(int $id);
}