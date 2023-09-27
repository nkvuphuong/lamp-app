<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Predis\Client;

try {
    $client = new Client([
        'host' => 'redis',
        'port' => 6379,
        'read_write_timeout' => 0
    ]);

//    dd($client->getConnection());

    $pubsub = $client->pubSubLoop();

    // Subscribe to your channels
    $pubsub->subscribe('control_channel', 'notifications');

    // Start processing the pubsub messages. Open a terminal and use redis-cli
    // to push messages to the channels. Examples:
    //   redis-cli PUBLISH notifications "this is a test"
    //   redis-cli PUBLISH control_channel quit_loop
    foreach ($pubsub as $message) {
        switch ($message->kind) {
            case 'subscribe':
                echo "Subscribed to {$message->channel}", PHP_EOL;
                break;

            case 'message':
                if ($message->channel == 'control_channel') {
                    if ($message->payload == 'quit_loop') {
                        echo 'Aborting pubsub loop...', PHP_EOL;
                        $pubsub->unsubscribe();
                    } else {
                        echo "Received an unrecognized command: " . json_encode($message->payload) . ".", PHP_EOL;
                    }
                } else {
                    echo "Received the following message from {$message->channel}:",
                    PHP_EOL, "  " . json_encode($message->payload), PHP_EOL, PHP_EOL;
                }
                break;
        }
    }
} catch (Exception $e) {
    dd($e->getMessage());
}
