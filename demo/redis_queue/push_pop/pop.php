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

    /*if ($client->isConnected())*/ {

        echo "[*] Connected\n";

        while (true) {
            // Lấy công việc từ đầu hàng đợi
            $jobData = $client->rpop('myqueue');

            if ($jobData !== null) {
                // Xử lý công việc ở đây
                echo "[+] Processing job: $jobData\n";
//                sleep(2);
                echo "[-] Processed: $jobData\n";
                echo "==============================================\n";
            } else {
                // Nếu hàng đợi trống, chờ một khoảng thời gian trước khi thử lại
                sleep(5);
                echo "[*] Retry\n";
            }
        }
    } /*else {
        echo "[*] Connection failed\n";
    }*/
} catch (Exception $e) {
    echo "[*] Connection error: {$e->getMessage()}\n";
}
