<?php

namespace demo\lib;

use PhpAmqpLib;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Exception;

class rabbit_queue
{
    private AMQPStreamConnection $connection;
    private PhpAmqpLib\Channel\AMQPChannel $channel;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = new AMQPStreamConnection($_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_USER'], $_ENV['RABBITMQ_PASS']);
        echo 'Rabbit queue connected' . PHP_EOL;
        $this->channel = $this->connection->channel();
        echo 'Rabbit channel connected' . PHP_EOL;
    }

    /**
     * @return AbstractChannel|\PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel(): \PhpAmqpLib\Channel\AMQPChannel|AbstractChannel
    {
        return $this->channel;
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        echo 'Close connection' . PHP_EOL;
        $this->channel->close();
        $this->connection->close();
    }
}
