<?php

use PhpAmqpLib\Message\AMQPMessage;

require 'init.php';

$redisQueueClient = (new \demo\lib\redis())->getClient();

try {
    $designs = json_decode(file_get_contents("https://picsum.photos/v2/list?page=2&limit=100"), 1, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    dd($e->getMessage());
}
$faker = Faker\Factory::create();


for ($i = 0; $i < 1000; $i++) {
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

    echo "[+] Published order: #{$order['uuid']}" . PHP_EOL;

    $creatingOrderMessages = json_encode($order, JSON_THROW_ON_ERROR);
    $redisQueueClient->lpush('order.create', [$creatingOrderMessages]);
}

