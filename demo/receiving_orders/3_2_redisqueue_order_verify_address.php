<?php
require 'init.php';

try {
    $redisClient = (new \demo\lib\redis())->getClient();

    while (true) {
        $jobData = $redisClient->rpop('order.verify_address');

        if ($jobData !== null) {

            $jobData = json_decode($jobData, 1, 512, JSON_THROW_ON_ERROR);

            echo "[+] Verifying address order {$jobData['uuid']}\n";

            echo "  [-] Call to API ... \n";
            $sleepTime = rand(0, 2);
            sleep($sleepTime);
            echo "  [-] Updated to DB {$sleepTime}s \n";

        } else {
            // Nếu hàng đợi trống, chờ một khoảng thời gian trước khi thử lại
            sleep(5);
            echo "[*] Retry after 5s\n";
        }
    }

} catch (Exception $e) {
    dd($e->getMessage());
}
