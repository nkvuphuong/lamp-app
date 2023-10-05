<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Predis\Client;

try {
    $client = new Client([
        'host' => 'redis',
        'port' => 6379,
        'read_write_timeout' => 0
    ]);

    // Use only one instance of DateTime, we will update the timestamp later.
    $timestamp = new DateTime();

    foreach (($monitor = $client->monitor()) as $event) {
        $timestamp->setTimestamp((int) $event->timestamp);

        // If we notice a ECHO command with the message QUIT_MONITOR, we stop the
        // monitor consumer and then break the loop.
        if ($event->command === 'ECHO' && $event->arguments === '"QUIT_MONITOR"') {
            echo 'Exiting the monitor loop...', PHP_EOL;
            $monitor->stop();
            break;
        }

        echo "* Received {$event->command} on DB {$event->database} at {$timestamp->format(DateTime::W3C)}", PHP_EOL;
        if (isset($event->arguments)) {
            echo "    Arguments: {$event->arguments}", PHP_EOL;
        }

       dump($event);
    }

} catch (Exception $e) {
    dd($e->getMessage());
}
