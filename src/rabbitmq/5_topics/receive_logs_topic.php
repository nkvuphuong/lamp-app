<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $exchangeName = 'topic_logs';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->exchange_declare($exchangeName, 'topic', false, false, false);
    list($queueName, ,) = $channel->queue_declare("", false, false, true, false);

    $binding_keys = array_slice($argv, 1);
    if (empty($binding_keys)) {
        file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
        exit(1);
    }

    foreach ($binding_keys as $binding_key) {
        $channel->queue_bind($queueName, $exchangeName, $binding_key);
    }

    echo " [*] Waiting for logs. To exit press CTRL+C\n";

    $callback = function ($msg) {
        sleep(rand(1, 3));
        echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
    };

    $channel->basic_consume($queueName, '', false, true, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
