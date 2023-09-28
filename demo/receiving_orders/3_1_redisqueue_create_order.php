<?php
require 'init.php';

try {
    $redisClient = (new \demo\lib\redis())->getClient();

    while (true) {
        $jobData = $redisClient->rpop('order.create');

        if ($jobData !== null) {
            $jobData = json_decode($jobData, 1, 512, JSON_THROW_ON_ERROR);

            $sleepTime = rand(0, 2);

            sleep($sleepTime);

            echo "  [-] Insert order {$jobData['uuid']} to DB ($sleepTime\s)\n";

        } else {
            // Nếu hàng đợi trống, chờ một khoảng thời gian trước khi thử lại
            sleep(5);
            echo "[*] Retry after 5s\n";
        }
    }

} catch (Exception $e) {
    dd($e->getMessage());
}
