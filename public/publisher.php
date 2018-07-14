<?php

require_once 'bootstrap.php';

$to = $_GET['to'] ?: 'all';

$exchange->publish(
    json_encode(['message' => $_GET['text'], 'from' => $_GET['from'], 'to' => $to]),
    $to
);

$connection->disconnect();
