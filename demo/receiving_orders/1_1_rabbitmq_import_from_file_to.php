<?php

use PhpAmqpLib\Message\AMQPMessage;

require 'init.php';

$queueConnection = new \src\lib\rabbit_queue();
$channel = $queueConnection->getChannel();
$routingKey = 'importing_orders';
echo '[*] Declaring queue' . PHP_EOL;
$channel->queue_declare($routingKey, false, true, false, false);

try {
    $designs = json_decode(file_get_contents("https://picsum.photos/v2/list?page=2&limit=100"), 1, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    dd($e->getMessage());
}
$faker = Faker\Factory::create();


/**
 * Giả sử có 10 file trên S3 mỗi file 10k dòng
 */
for ($fileNum = 0; $fileNum < 10; $fileNum++) {

    echo "[*] Importing file #$fileNum" . PHP_EOL;

    for ($i = 0; $i < 100; $i++) {
        $order = [
            'uuid' => uuid_create(),
            'seller_id' => rand(1, 1000),
            'date' => date('c'),
            'shipping_to' => [
                'name' => $faker->name,
                'email' => $faker->email,
                'address' => $faker->address,
                'phone' => $faker->phoneNumber,
                'country_code' => $faker->countryCode,
                'zip_code' => $faker->postcode
            ]
        ];

        $order['items'] = [];

        for ($j = 1; $j <= rand(1, 10); $j++) {
            $order['items'][] = [
                'product_id' => random_int(1, 5),
                'quantity' => random_int(1, 10),
                'design_url' => $designs[random_int(0, 99)]['download_url']
            ];
        }

        try {
            $msg = new AMQPMessage(json_encode($order, JSON_THROW_ON_ERROR), array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

            /**
             * Process by batch
             */
            $channel->batch_basic_publish($msg, '', $routingKey);
        } catch (JsonException $e) {
            dd($e->getMessage());
        }

        echo "[+] Added order: #{$order['uuid']}" . PHP_EOL;
    }

    $channel->publish_batch();
    echo "[*] Published data of file #$fileNum" . PHP_EOL;
}

$channel->close();


