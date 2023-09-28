<?php

namespace demo\lib;

use Illuminate\Support\Str;
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
        $rs = 0;

        $redis = new redis();

        $cacheName = Str::slug(__FILE__ . __FUNCTION__);

        $res = file_get_contents($url = "http://{$_ENV['RABBITMQ_USER']}:{$_ENV['RABBITMQ_PASS']}@{$_ENV['RABBITMQ_HOST']}:15672/api/queues");

        $maxSize = 100;

        $cacheVal = $redis->load($cacheName);

        if (empty($cacheVal) && $stats = json_decode($res, 1, 512, JSON_THROW_ON_ERROR)) {
            $messagesReady = $stats[0]['messages_ready'];
            $availableConsumers = $stats[0]['consumers'];
            $maxConsumers = ceil($messagesReady / $maxSize);

            $rs = $maxConsumers - $availableConsumers;
        } else {
            $rs = $cacheVal;
        }

        $redis->save($cacheName, $rs, 10);

        return $rs;
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
