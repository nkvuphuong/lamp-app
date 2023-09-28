<?php

namespace demo\lib;

use Predis\Client;

class redis
{
    private Client $client;


    public function __construct()
    {
        $this->client = new Client(
            [
                'host' => $_ENV['REDIS_HOST'],
                'port' => $_ENV['REDIS_PORT']
            ]
        );

        echo 'Redis queue connected' . PHP_EOL;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param $key
     * @param $value
     * @param $expired
     * @return void
     */
    public function save($key, $value, $expired = 0)
    {
        $this->client->set($key, $value);

        if (!empty($expired)) {
            $this->client->expire($key, $expired);
        }
    }

    /**
     * @param $key
     * @return string|null
     */
    public function load($key)
    {
        return $this->client->get($key);
    }
}