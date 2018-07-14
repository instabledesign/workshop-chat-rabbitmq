<?php

require_once 'bootstrap.php';

$from = $_GET['from'];

$queue = new \AMQPQueue(new \AMQPChannel($connection));
$queue->setName($from);
$queue->setFlags(AMQP_AUTODELETE);
$queue->setArgument('x-expires', 1000);
$queue->declareQueue();
if (isset($_GET['join'])) {
    $join = $_GET['join'];
    $queue->bind('chat', null, ['to' => $join]);

    $exchange->publish(
        json_encode(['action' => sprintf('%s join channel %s.', $from, $join)]),
        null,
        AMQP_NOPARAM,
        [
            'headers' => [
                'to' => $join,
            ],
        ]
    );
}

if (isset($_GET['leave'])) {
    $leave = $_GET['leave'];
    $exchange->publish(
        json_encode(['action' => sprintf('%s leave channel %s.', $from, $leave)]),
        null,
        AMQP_NOPARAM,
        [
            'headers' => [
                'to' => $leave,
            ],
        ]
    );

    $queue->unbind('chat', null, ['to' => $leave]);
}

$connection->disconnect();
