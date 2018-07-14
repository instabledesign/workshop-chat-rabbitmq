<?php

require_once 'bootstrap.php';

// Init SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Setting this header instructs Nginx to disable fastcgi_buffering and disable

$user = $_GET['username'];

/**
 * Init the user queue HERE
 */
$queue = new \AMQPQueue($channel);
$queue->setName($user);
$queue->setFlags(AMQP_AUTODELETE);
$queue->setArgument('x-expires', 1000);
$queue->declareQueue();
$queue->bind('chat', 'all');

// send join message
$exchange->publish(json_encode(['action' => sprintf('%s join.', $user)]), 'all');

// Return strictly false if you want to stop consumer
$consumer = function ($envelope) use ($queue) {
    try {
        echo sprintf(
            "id: %d\nevent: %s\ndata: %s\n\n",
            $envelope->getDeliveryTag(),
            'message',
            $envelope->getBody()
        );
        ob_flush();
        flush();
        $queue->ack($envelope->getDeliveryTag());
    } catch (\Exception $exception) {
        $queue->nack($envelope->getDeliveryTag());
    }
};

// When the script end
register_shutdown_function(function () use ($connection) {
    $connection->disconnect();
});

// Consumer loop
$pingTime = time();
while (true) {
    // Iterate over message until queue empty
    while (false !== $message = $queue->get()) {
        if (false === $consumer($message)) {
            break 2;
        }
    }

    if ($pingTime + 5 < time()) {
        $pingTime = time();
        echo "event: ping\n\n";
        ob_flush();
        flush();
    }

    // to slow down the infinite loop
    usleep(100000);
}
