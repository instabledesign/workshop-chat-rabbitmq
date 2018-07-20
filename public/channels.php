<?php

require_once 'bootstrap.php';

$from = $_GET['from'];

$queue = new \AMQPQueue($channel);
$queue->setName($from);
$queue->setFlags(AMQP_AUTODELETE);
$queue->setArgument('x-expires', 1000);
$queue->declareQueue();
if (isset($_GET['join'])) {
    $join = $_GET['join'];
    $queue->bind($exchange->getName(), $join);

    $exchange->publish(
        json_encode(['action' => sprintf('%s join channel %s.', $from, $join)]),
        $join
    );
}

if (isset($_GET['leave'])) {
    $leave = $_GET['leave'];
    $exchange->publish(
        json_encode(['action' => sprintf('%s leave channel %s.', $from, $leave)]),
        $leave
    );

    $queue->unbind($exchange->getName(), $leave);
}

$connection->disconnect();
