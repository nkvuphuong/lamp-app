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

            echo "  [+] Insert ordered {$jobData['uuid']} to DB ($sleepTime\s)\n";

            /**
             * Publish to Redis queue to verify address
             */
            $verifyingAddressMessage = json_encode([
                'uuid' => $jobData['uuid'],
                'shipping_to' => $jobData['shipping_to'],
            ], JSON_THROW_ON_ERROR);
            $redisClient->lpush('order.verify_address', [$verifyingAddressMessage]);
            echo "      [-] Published to Redis queue to verify address \n";

            /**
             * Publish to Redis queue to download design image
             */
            $downloadingDesignImageMessage = json_encode([
                'uuid' => $jobData['uuid'],
                'items' => $jobData['items'],
            ], JSON_THROW_ON_ERROR);
            $redisClient->lpush('order.download_design', $downloadingDesignImageMessage);
            echo "      [-] Published to Redis queue to download design image \n";

        } else {
            // Nếu hàng đợi trống, chờ một khoảng thời gian trước khi thử lại
            sleep(5);
            echo "[*] Retry after 5s\n";
        }
    }

} catch (Exception $e) {
    dd($e->getMessage());
}
