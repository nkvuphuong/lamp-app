<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $exchangeName = 'topic_logs';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->exchange_declare($exchangeName, 'topic', false, false, false);

    $facilities = ['sys', 'be', 'fe', 'db'];
    $severities = ['info', 'warning', 'error'];

    for ($i = 0; $i < 100; $i++) {
        $facility = $facilities[rand(0,3)];
        $severity = $severities[rand(0,2)];
        $routingKey = "$facility.$severity";
        $data = "$routingKey message No#$i";
        $msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($msg, $exchangeName, $routingKey);
        echo "Sent message: <<$data>> \n";
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
