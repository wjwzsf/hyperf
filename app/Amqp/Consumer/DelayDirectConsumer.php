<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Model\OrderTest;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerDelayedMessageTrait;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use PhpAmqpLib\Message\AMQPMessage;
use App\Model\HyOrder;
use Hyperf\Amqp\Message\Type;


#[Consumer(exchange: 'ext.hyperf.delay', routingKey: '', queue: 'queue.hyperf.delay', nums: 1)]
class DelayDirectConsumer extends ConsumerMessage
{
    use ProducerDelayedMessageTrait;
    use ConsumerDelayedMessageTrait;
    protected string $exchange = 'ext.hyperf.delay';
    protected ?string $queue = 'queue.hyperf.delay';
    protected string $type = Type::DIRECT; //Type::FANOUT;
    protected string|array $routingKey = '';
    public function consumeMessage($data, AMQPMessage $message): string
    {
        $data = json_decode($data,true);
        var_dump($data);
        if($data['orderCode']){
            $order = new OrderTest();
            //根据条件取消订单
            $order = $order->where("orderCode","=",$data['orderCode'] )->where("status",1)->first();
            if($order){
                $order->status = 9;
                $order->save();
            }
        }
        return Result::ACK;
    }
}