<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->queue_declare('rpc_queue', false, false, false, false);

    function fib($n)
    {
        if ($n == 0) {
            return 0;
        }
        if ($n == 1) {
            return 1;
        }
        return fib($n-1) + fib($n-2);
    }

    echo " [x] Awaiting RPC requests\n";
    $callback = function ($req) {
        $n = (int)$req->body;
        echo ' [.] fib(', $n, ")\n";

        $msg = new AMQPMessage(
            (string) fib($n),
            array('correlation_id' => $req->get('correlation_id'))
        );

        $req->getChannel()->basic_publish(
            $msg,
            '',
            $req->get('reply_to')
        );
        $req->ack();
    };

    $channel->basic_qos(null, 1, null);
    $channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
