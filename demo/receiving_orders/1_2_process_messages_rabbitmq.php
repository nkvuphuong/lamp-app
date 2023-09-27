<?php
require 'init.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

try {
    $routingKey = 'importing_orders';
    $queueConnection = new \src\lib\rabbit_queue();
    $channel = $queueConnection->getChannel();

    $durable = true; //make sure that the queue will survive a RabbitMQ node restart
    $channel->queue_declare($routingKey, false, $durable, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = static function (PhpAmqpLib\Message\AMQPMessage $msg) {
        $sleepingTime = random_int(1, 3);
        echo " [x] Received: waiting for $sleepingTime seconds ", $msg->body, "\n";

//        if ($sleepingTime === 3) {
//            throw new RuntimeException('Die');
//        }

        sleep($sleepingTime = 0);
        echo " [x] Done: " . date('c') . "\n";
        $msg->ack();
    };

    $channel->basic_qos(null, 1, null); // (Fair dispatch) This tells RabbitMQ not to give more than one message to a worker at a time. Or, in other words, don't dispatch a new message to a worker until it has processed and acknowledged the previous one. Instead, it will dispatch it to the next worker that is not still busy.
    $channel->basic_consume($routingKey, '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
