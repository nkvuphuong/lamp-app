<?php
require 'init.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

try {
    $routingKey = 'importing_orders';
    $rabbitmqConnection = new \src\lib\rabbit_queue();
    $channel = $rabbitmqConnection->getChannel();

    $redisQueueClient = (new \demo\lib\redis())->getClient();

    $durable = true; //make sure that the queue will survive a RabbitMQ node restart
    $channel->queue_declare($routingKey, false, $durable, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $creatingOrderMessages = $verifyingAddressMessages = $downloadingDesignImageMessages = [];

    $callback = static function (PhpAmqpLib\Message\AMQPMessage $msg) use (&$creatingOrderMessages, &$verifyingAddressMessages, &$downloadingDesignImageMessages, $redisQueueClient) {
        $redisQueueSize = 10;
        $sleepingTime = random_int(0, 1);
        $orderData = json_decode($msg->body, 1);
        echo " [x] Received: waiting for $sleepingTime seconds ", $orderData['uuid'], "\n";

//        if ($sleepingTime === 3) {
//            throw new RuntimeException('Die');
//        }

        /**
         * Publish to Redis queue to create orders
         */
        $creatingOrderMessages[] = json_encode($orderData, JSON_THROW_ON_ERROR);

        if (count($creatingOrderMessages) >= $redisQueueSize) {
            echo "  [-] Publish to Redis queue to create orders \n";
            $redisQueueClient->lpush('order.create', $creatingOrderMessages);
            $creatingOrderMessages = [];
        }

        /**
         * Publish to Redis queue to verify address
         */
        $verifyingAddressMessages[] = json_encode([
            'uuid' => $orderData['uuid'],
            'shipping_to' => $orderData['shipping_to'],
        ], JSON_THROW_ON_ERROR);

        if (count($verifyingAddressMessages) >= $redisQueueSize) {
            echo "  [-] Publish to Redis queue to verify address \n";
            $redisQueueClient->lpush('order.verify_address', $verifyingAddressMessages);
            $verifyingAddressMessages = [];
        }

        /**
         * Publish to Redis queue to download design image
         */
        $downloadingDesignImageMessages[] = json_encode([
            'uuid' => $orderData['uuid'],
            'items' => $orderData['items'],
        ]);

        if (count($downloadingDesignImageMessages) >= $redisQueueSize) {
            echo "  [-] Publish to Redis queue to download design image \n";
            $redisQueueClient->lpush('order.download_design', $downloadingDesignImageMessages);
            $downloadingDesignImageMessages = [];
        }

        sleep($sleepingTime);
        echo " [x] Done: " . date('c') . "\n";
        $msg->ack();
    };

    if (!empty($creatingOrderMessages)) {
        echo "  [-] Publish to Redis queue to create orders * \n";
        $redisQueueClient->lpush('order.create', $creatingOrderMessages);
        $creatingOrderMessages = [];
    }

    if (!empty($verifyingAddressMessages)) {
        echo "  [-] Publish to Redis queue to verify address * \n";
        $redisQueueClient->lpush('order.verify_address', $verifyingAddressMessages);
        $creatingOrderMessages = [];
    }

    if (!empty($downloadingDesignImageMessages)) {
        echo "  [-] Publish to Redis queue to download design image * \n";
        $redisQueueClient->lpush('order.download_design', $downloadingDesignImageMessages);
        $creatingOrderMessages = [];
    }

    $channel->basic_qos(null, 1, null); // (Fair dispatch) This tells RabbitMQ not to give more than one message to a worker at a time. Or, in other words, don't dispatch a new message to a worker until it has processed and acknowledged the previous one. Instead, it will dispatch it to the next worker that is not still busy.
    $channel->basic_consume($routingKey, '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
