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
}