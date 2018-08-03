<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$redis = require __DIR__ . '/redis.php';

$config = [
    'db'     => $db,
    'redis'  => $redis,
    'params' => $params,
];

return $config;