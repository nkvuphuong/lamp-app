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
    private $consumerId;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->consumerId = uuid_create();
        $this->connection = new AMQPStreamConnection($_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_USER'], $_ENV['RABBITMQ_PASS']);
        echo 'Rabbit queue connected' . PHP_EOL;
        $this->channel = $this->connection->channel();
        static::addToListConsumers($this->consumerId);
        echo 'Rabbit channel connected' . PHP_EOL;
    }

    /**
     * @return string|null
     */
    public function getConsumerId()
    {
        return $this->consumerId;
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
    static function needConsumers()
    {
        $rs = 0;
        $res = file_get_contents($url = "http://{$_ENV['RABBITMQ_USER']}:{$_ENV['RABBITMQ_PASS']}@{$_ENV['RABBITMQ_HOST']}:15672/api/queues");
        $maxSize = 100;

        if ($stats = json_decode($res, 1, 512, JSON_THROW_ON_ERROR)) {
            $messagesReady = $stats[0]['messages_ready'];
            $availableConsumers = $stats[0]['consumers'];
            $maxConsumers = ceil($messagesReady / $maxSize);
            $rs = $maxConsumers - $availableConsumers;
        }

        return $rs;
    }

    /**
     * @return array|mixed
     * @throws \JsonException
     */
    static function listConsumers()
    {
        $redisCache = (new redis());
        $rabbitConsumersCacheName = 'rabbit_consumers';
        $rabbitConsumers = [];
        if ($cacheValue = $redisCache->load($rabbitConsumersCacheName)) {
            $rabbitConsumers = json_decode($cacheValue, 1, 512, JSON_THROW_ON_ERROR);
        }
        return $rabbitConsumers;
    }

    static function addToListConsumers($consumerId)
    {
        $redisCache = (new redis());
        $rabbitConsumersCacheName = 'rabbit_consumers';
        $rabbitConsumers = static::listConsumers();
        $rabbitConsumers[$consumerId] = date('c');
        $redisCache->save($rabbitConsumersCacheName, json_encode($rabbitConsumers, JSON_THROW_ON_ERROR));

        echo "[*] Added {$consumerId} to list consumers\n";
        return $rabbitConsumers;
    }

    static function removeFromListConsumers($consumerId)
    {
        $redisCache = (new redis());
        $rabbitConsumersCacheName = 'rabbit_consumers';
        $rabbitConsumers = static::listConsumers();
        if (isset($rabbitConsumers[$consumerId])) {
            unset($rabbitConsumers[$consumerId]);
        }
        $redisCache->save($rabbitConsumersCacheName, json_encode($rabbitConsumers, JSON_THROW_ON_ERROR));
        echo "[*] Removed {$consumerId} from list consumers\n";
        return $rabbitConsumers;
    }

    /**
     * @param $consumerId
     * @return bool
     * @throws \JsonException
     */
    static function isDestroyed($consumerId)
    {
        $rabbitConsumers = static::listConsumers();
        return !isset($rabbitConsumers[$consumerId]);
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
        static::removeFromListConsumers($this->consumerId);
        echo 'Rabbit connection closed' . PHP_EOL;
    }
}
