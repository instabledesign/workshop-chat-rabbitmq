<?php

require_once 'bootstrap.php';

$to = $_GET['to'] ?: 'all';
$from = $_GET['from'];
$exchange->publish(
    json_encode(['message' => $_GET['text'], 'from' => $from, 'to' => $to]),
    null,
    AMQP_NOPARAM,
    [
        'headers' => [
            'to' => $to,
            'from' => $from,
        ]
    ]
);

$connection->disconnect();
