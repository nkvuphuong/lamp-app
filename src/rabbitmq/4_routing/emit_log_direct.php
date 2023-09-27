<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $exchangeName = 'direct_logs';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->exchange_declare($exchangeName, 'direct', false, false, false);

    $severities = ['info', 'warning', 'error'];

    for ($i = 0; $i < 100; $i++) {
        $severity = $severities[rand(0,2)];
        $data = "$severity message No#$i";
        $msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($msg, $exchangeName, $severity);
        echo "Sent message: <<$data>> \n";
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
