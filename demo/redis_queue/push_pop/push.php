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

    /*$client = new Client([
        'host' => 'demoqueue-0001-001.elku6n.0001.apse2.cache.amazonaws.com',
        'port' => 6379,
        'read_write_timeout' => 0
    ]);*/


    $messages = [];
    for ($i = 0; $i <= 100; $i++) {
        $messages[] = "Msg No.$i";
    }

    dd($client->lpush('myqueue', $messages));
} catch (Exception $e) {
    dd($e->getMessage());
}
