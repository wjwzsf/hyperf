<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Model\Member;
use App\Model\OrderTest;
use App\Service\UserService;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\ResponseInterface;
#[AutoController]
class IndexController extends AbstractController
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * 通过 `#[Inject]` 注解注入由注解声明的属性类型对象
     * 需要引入 use Hyperf\Di\Annotation\Inject;
     * @var UserService
     */
    #[Inject]
    private $userService;
    //    /**
    //     * @var UserService
    //     */
    //    private $userService;
    //
    //    //通过构造函数的参数上声明参数类型完成自动注入
    //    public function __construct(UserService $userService)
    //    {
    //        $this->userService=$userService;
    //    }
    public function index()
    {
        //直接使用
        return $this->userService->getInfoById(1);
        //        $user = $this->request->input('user', 'Hyperf');
        //        $method = $this->request->getMethod();
        //
        //        return [
        //            'method' => $method,
        //            'message' => "Hello {$user}.",
        //        ];
    }
    public function getindex()
    {
        return Member::find(7293);
    }
    public function setredis()
    {
        $redis = ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
        return $redis->set("time", "看看现在时间 " . date("Y-m-d H:i:s", time()));
    }
    public function getredis(ResponseInterface $response)
    {
        $redis = ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
        return $response->json($redis->get('time'));
    }
}