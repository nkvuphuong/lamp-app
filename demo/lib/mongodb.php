<?php

namespace demo\lib;

use MongoDB\Client;
use MongoDB\Driver\ServerApi;

class mongodb
{
    private Client $client;

    public function __construct()
    {
        // Specify Stable API version 1
        $apiVersion = new ServerApi(ServerApi::V1);
        $uri = sprintf("mongodb://%s:%s/?authSource=admin", $_ENV['MONGODB_HOST'], $_ENV['MONGODB_PORT']);
        $uriOptions = [
            'username' => $_ENV['MONGODB_USERNAME'],
            'password' => $_ENV['MONGODB_PASSWORD'],
        ];
        $this->client = new Client($uri, $uriOptions, ['serverApi' => $apiVersion]);
        // Send a ping to confirm a successful connection
        $this->client->selectDatabase('admin');
        echo "Pinged your deployment. You successfully connected to MongoDB!\n";
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
