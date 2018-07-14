<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Setting this header instructs Nginx to disable fastcgi_buffering and disable

$amqpExtension = extension_loaded('amqp');
$amqpConnection = $amqpCreateExchange = $amqpCreateQueue = $amqpBindQueue = $amqpExchangePublish = $amqpQueueGet = false;

if ($amqpExtension) {
    try {
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
        $amqpConnection = $connection->connect();
    } catch (\Exception $connectionException) {
    }
    if ($amqpConnection && !isset($connectionException)) {
        try {
            $channel = new \AMQPChannel($connection);
            $exchange = new \AMQPExchange($channel);
            $exchange->setName('config-test');

            $exchange->setType(AMQP_EX_TYPE_FANOUT);
            $exchange->setFlags(AMQP_AUTODELETE);
            $amqpCreateExchange = $exchange->declareExchange();
        } catch (\Exception $exchangeException) {
        }
    }
    if (isset($channel) && !isset($exchangeException)) {
        try {
            $queue = new \AMQPQueue($channel);
            $queue->setName('config-test');
            $queue->setFlags(AMQP_AUTODELETE);
            $queue->setArgument('x-expires', 1000);
            $queue->declareQueue();
            $amqpCreateQueue = true;
        } catch (\Exception $queueException) {
        }
    }
    if (isset($queue) && !isset($queueException)) {
        try {
            $amqpBindQueue = $queue->bind('config-test');
        } catch (\Exception $queueBindException) {
        }
    }
    if (isset($exchange) && !isset($exchangeException)) {
        try {
            $amqpExchangePublish = $exchange->publish('config-test');
        } catch (\Exception $exchangePublishException) {
        }
    }
    if (isset($queue) && !isset($exchangePublishException)) {
        try {
            $amqpQueueGet = $queue->get();
        } catch (\Exception $queueGetException) {
        }
    }
}

printf("AMQP %s %s\r\n", $amqpConnection && $amqpCreateExchange && $amqpCreateQueue && $amqpBindQueue && $amqpExchangePublish && $amqpQueueGet ? '✔️' : '❌', str_repeat('-', 40));
printf(" ├ Extension %s\r\n", $amqpExtension ? '✔️' : '❌');
printf(" ├ Connection %s %s\r\n", $amqpConnection ? '✔️' : '❌', isset($connectionException) ? '(' . $connectionException->getMessage() . ')' : '');
printf(" ├ Create exchange %s %s\r\n", $amqpCreateExchange ? '✔️' : '❌', isset($exchangeException) ? '(' . $exchangeException->getMessage() . ')' : '');
printf(" ├ Create queue %s %s\r\n", $amqpCreateQueue ? '✔️' : '❌', isset($queueException) ? '(' . $queueException->getMessage() . ')' : '');
printf(" ├ Bind queue %s %s\r\n", $amqpBindQueue ? '✔️' : '❌', isset($queueBindException) ? '(' . $queueBindException->getMessage() . ')' : '');
printf(" ├ Publish exchange %s %s\r\n", $amqpExchangePublish ? '✔️' : '❌', isset($exchangePublishException) ? '(' . $exchangePublishException->getMessage() . ')' : '');
printf(" └ Queue get %s %s\r\n\r\n", $amqpQueueGet ? '✔️' : '❌', isset($queueGetException) ? '(' . $queueGetException->getMessage() . ')' : '');


$string_length = 10;
$streamDuration = 3;
printf("STREAM %s\r\n", str_repeat('-', 40));
printf("  The stream data must appear over the time.\r\n  Stream data doesn't work if you see the page load at once.\r\n\r\n");
printf("Begin stream text during %dsec with %d character string...\r\n", $streamDuration, $string_length);
for ($i = 1; $i <= $streamDuration; $i++) {
    printf(" ├ %d %s\r\n", $i, str_repeat('.', $string_length));
    ob_flush();
    flush();
    sleep(1);
}
printf(" └ End test.\r\n\r\n");
