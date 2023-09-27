<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

try {
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->queue_declare('greeting', false, false, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = function ($message) {
        echo "[x] Received message ", $message->getBody(), "\n";
    };

    $channel->basic_consume('greeting', '', false, true, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
