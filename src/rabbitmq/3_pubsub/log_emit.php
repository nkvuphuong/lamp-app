<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $exchangeName = 'logs';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->exchange_declare($exchangeName, 'fanout', false, false, false);

    for ($i = 0; $i < 10; $i++) {
        $data = 'Log message No#' . $i;
        $msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($msg, $exchangeName);
        echo "Sent message: <<$data>> \n";
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
