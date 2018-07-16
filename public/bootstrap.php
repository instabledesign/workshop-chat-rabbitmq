<?php

if (!extension_loaded('amqp')) {
    die('Extension amqp require.');
}

// Create amqp connection
$connection = new \AMQPConnection(array(
    'host' => 'slack_amqp',
    'port' => 5672,
    'vhost' => '/',
    'login' => 'guest',
    'password' => 'guest',
    'read_timeout' => 2,
    'write_timeout' => 2,
    'connect_timeout' => 10,
));
$connection->connect();

$channel = new \AMQPChannel($connection);

/**
 * Init exchange chat HERE
 */
$exchange = new \AMQPExchange($channel);
$exchange->setName('chat');
$exchange->setType(AMQP_EX_TYPE_HEADERS);
$exchange->setFlags(AMQP_DURABLE);
$exchange->declareExchange();

//retry exchange
//$exchange = new \AMQPExchange($channel);
//$exchange->setName('internal_waiting_2');
//$exchange->setType(AMQP_EX_TYPE_FANOUT);
//$exchange->setFlags(AMQP_DURABLE + AMQP_INTERNAL);
//$exchange->declareExchange();
//$queue2 = new \AMQPQueue($channel);
//$queue2->setName('internal_waiting_2');
//$queue2->setFlags(AMQP_INTERNAL);
//$queue2->setArgument('x-dead-letter-exchange', '');
//$queue2->setArgument('x-message-ttl', 2000);
//$queue2->bind('internal_waiting_2');
//$queue2->declareQueue();
