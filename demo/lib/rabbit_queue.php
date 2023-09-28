<?php

namespace demo\lib;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Exception;

class rabbit_queue
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

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
     * @return AbstractChannel|AMQPChannel
     */
    public function getChannel(): AMQPChannel|AbstractChannel
    {
        return $this->channel;
    }

    /**
     * @return int
     * @throws \JsonException
     */
    static function checkConsumer()
    {
        $res = file_get_contents("http://{$_ENV['RABBITMQ_HOST']}:{$_ENV['RABBITMQ_USER']}@{$_ENV['RABBITMQ_PASS']}:{$_ENV['RABBITMQ_PORT']}/api/queues");

        $maxSize = 100;

        if ($stats = json_decode($res, 1, 512, JSON_THROW_ON_ERROR)) {
            $messagesReady = $stats[0]['messages_ready'];
            $availableConsumers = $stats[0]['consumers'];
            $maxConsumers = ceil($messagesReady / $maxSize);

            return $maxConsumers - $availableConsumers;
        }

        return 0;
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        echo 'Rabbit connection closed' . PHP_EOL;
        $this->channel->close();
        $this->connection->close();
    }
}
