<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->queue_declare('greeting', false, false, false, false);

    $msgBody = 'Hi there: ' . date('c');
    $msg = new AMQPMessage($msgBody);

    $channel->basic_publish($msg, '', 'greeting');

    echo "Sent message: <<$msgBody>>";
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
