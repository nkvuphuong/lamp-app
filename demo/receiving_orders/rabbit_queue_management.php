<?php

use demo\lib\rabbit_queue;

require 'init.php';

while (true) {
    $needConsumers = rabbit_queue::needConsumers();
    echo "[*] \$needConsumers = $needConsumers \n";

    if ($needConsumers < 0) {
        $needDestroy = abs($needConsumers);
        $listConsumers = rabbit_queue::listConsumers();
        $listConsumersCnt = count($listConsumers);

        $needDestroy = ($needDestroy > $listConsumersCnt) ? ($listConsumersCnt - 1) : $needDestroy;
        echo "[*] \$needDestroy = $needDestroy \n";
        $removed = 0;
        foreach ($listConsumers as $consumerId => $date) {
            if ($removed === (int)$needDestroy) {
                break;
            }

            rabbit_queue::removeFromListConsumers($consumerId);
            $removed++;
            echo "[*] \$removed = $removed \n";
        }
    }

    sleep(10);
}


echo "[*] END";



