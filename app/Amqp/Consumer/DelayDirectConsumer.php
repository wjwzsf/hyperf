<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 消息消费demo
 * Class DelayDirectConsumer
 * @package App\Amqp\Consumer
 */
#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', name: "DelayDirectConsumer", nums: 1)]
class DelayDirectConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): string
    {
        return Result::ACK;
    }
}
