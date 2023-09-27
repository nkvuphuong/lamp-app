<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $exchangeName = 'direct_logs';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->exchange_declare($exchangeName, 'direct', false, false, false);
    list($queueName, ,) = $channel->queue_declare("", false, false, true, false);

    $severities = array_slice($argv, 1);
    if (empty($severities)) {
        file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
        exit(1);
    }

    foreach ($severities as $severity) {
        $channel->queue_bind($queueName, $exchangeName, $severity);
    }

    echo " [*] Waiting for logs. To exit press CTRL+C\n";

    $callback = function ($msg) {
        sleep(rand(1,3));
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
