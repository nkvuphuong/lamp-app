<?php
require 'init.php';

try {
    $redisClient = (new \demo\lib\redis())->getClient();

    while (true) {
        $jobData = $redisClient->rpop('order.download_design');

        if ($jobData !== null) {

            $jobData = json_decode($jobData, 1, 512, JSON_THROW_ON_ERROR);
            $orderItems = $jobData['items'];

            echo "[+] Downloading design {$jobData['uuid']}\n";

            foreach ($orderItems as $k => $item) {
                echo "  [-] Downloading ... {$item['design_url']} \n";
                $sleepTime = rand(0, 1);
                sleep($sleepTime);
                echo "  [-] Uploading to S3 {$sleepTime}s \n";
                $sleepTime = rand(1, 2);
                sleep($sleepTime);
                echo "  [-] Uploaded to DB \n";
            }


        } else {
            // Nếu hàng đợi trống, chờ một khoảng thời gian trước khi thử lại
            sleep(5);
            echo "[*] Retry after 5s\n";
        }
    }

} catch (Exception $e) {
    dd($e->getMessage());
}
