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
$exchange;//???