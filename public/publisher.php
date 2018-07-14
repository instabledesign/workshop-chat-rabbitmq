<?php

require_once 'bootstrap.php';

$exchange->publish(json_encode(['message' => $_GET['text'], 'from' => $_GET['from']]));

$connection->disconnect();
