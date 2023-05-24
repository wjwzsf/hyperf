<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;

/**
 * 投递消息demo
 * Class DelayDirectProducer
 * @package App\Amqp\Producer
 */
#[Producer(exchange: 'ext.hyperf.delay', routingKey: '')]
class DelayDirectProducer extends ProducerMessage
{
    use ProducerDelayedMessageTrait;
    protected string $exchange = "ext.hyperf.delay";
    protected string $type = Type::DIRECT;
    protected string|array $routingKey='';
    public function __construct($data)
    {
        $this->payload = $data;
    }
}
