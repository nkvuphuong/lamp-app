<?php
require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../demo/lib/mongodb.php';
require __DIR__.'/../../demo/lib/rabbit_queue.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();
