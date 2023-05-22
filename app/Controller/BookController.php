<?php


namespace App\Controller;

use App\Dao\BookDao;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller]
class BookController
{
    /**
     * User: wujiawei
     * DateTime: 2023/5/22 9:01
     * describe:测试 Book操作
     * @return mixed[]
     * @Get
     */
    #[GetMapping(path: "book/index")]
    public function getBookTitles(){
        $bookDao = new BookDao();
        $titles = $bookDao->getBookTitles();
        return $titles;
    }
}