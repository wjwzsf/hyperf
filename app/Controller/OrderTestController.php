<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Amqp\Producer\DelayDirectProducer;
use App\Model\OrderTest;
use Hyperf\Amqp\Producer;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Utils\ApplicationContext;

/**
 * Class OrderTestController
 * @package App\Controller
 */
#[AutoController]
class OrderTestController extends AbstractController
{

    public function createOrder(){
        $order = new OrderTest();
        $order_data = [
            'orderCode'=>time(),
            'status'=>1
        ];
        //发布到rabbitmq
        $json = json_encode($order_data);
        $message = new DelayDirectProducer($json);
        $message->setDelayMs(10*1000);//定时10s
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $result = $producer->produce($message);
        //插入数据库
        $order->orderCode = $order_data['orderCode'];
        $order->status = $order_data['status'];
        $order->save();
        return "success";
    }
}