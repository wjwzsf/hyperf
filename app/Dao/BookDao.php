<?php


namespace App\Dao;
use Hyperf\DbConnection\Db;

class BookDao
{
    /**
     * User: wujiawei
     * DateTime: 2023/5/22 8:59
     * describe: 获取code列表
     * @return mixed[]
     */
    public function getBookTitles()
    {
        return Db::table('code')->pluck('code')->toArray();
    }
}