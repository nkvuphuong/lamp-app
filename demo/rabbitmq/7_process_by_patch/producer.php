<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $routingKey = 'batch_task_queue';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $durable = true; //make sure that the queue will survive a RabbitMQ node restart
    $msg = null;
    $channel = $connection->channel();
    $channel->queue_declare($routingKey, false, $durable, false, false);
    for ($j = 0; $j < 40; $j++) {
        for ($i = 0; $i < 50000; $i++) {
            $data = "Task No#{$j}_{$i}";
            /*if ($msg instanceof AMQPMessage) {
                $msg->setBody($data);
            } else*/ {
                $msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
            }

            $channel->batch_basic_publish($msg, '', $routingKey);
            echo "Added message: <<$data>> \n";
        }
        $channel->publish_batch();
        echo "Published all messages\n";
    }
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
