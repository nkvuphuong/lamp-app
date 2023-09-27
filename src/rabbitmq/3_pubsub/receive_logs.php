<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $exchangeName = 'logs';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->exchange_declare($exchangeName, 'fanout', false, false, false);
    list($queueName, ,) = $channel->queue_declare("", false, false, true, false);
    $channel->queue_bind($queueName, 'logs');
    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = static function (PhpAmqpLib\Message\AMQPMessage $msg) {
        $sleepingTime = rand(1, 5);
        echo " [x] Received: waiting for $sleepingTime seconds ", $msg->body, "\n";
        sleep($sleepingTime);
        echo " [x] Done: " . date('c') . "\n";
        $msg->ack();
    };

    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
