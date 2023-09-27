<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Predis\Client;

try {

    $designs = json_decode(file_get_contents("https://picsum.photos/v2/list?page=2&limit=100"), 1, 512, JSON_THROW_ON_ERROR);

    $faker = Faker\Factory::create();

    $client = new Client([
        'host' => 'redis',
        'port' => 6379,
        'read_write_timeout' => 0
    ]);

    $messages = [];
    for ($i = 0; $i <= 200000; $i++) {
        $order = [
            'uuid' => uuid_create(),
            'seller_id' => random_int(1, 1000),
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

        for ($j = 1; $j <= random_int(1, 10); $j++) {
            $order['items'][] = [
                'product_id' => random_int(1, 5),
                'quantity' => random_int(1, 10),
                'design_url' => $designs[random_int(0, 99)]['download_url']
            ];
        }

        $messages[] = $order;
    }

    dd($client->lpush('myqueue', $messages));
} catch (Exception $e) {
    dd($e->getMessage());
}
