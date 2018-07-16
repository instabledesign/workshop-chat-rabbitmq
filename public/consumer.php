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
// add retry config HERE
$queue->setArgument('x-dead-letter-exchange', 'internal_waiting_2');
$queue->setArgument('x-dead-letter-routing-key', $user);
$queue->declareQueue();
$queue->bind('chat', null, ['to' => 'all']);
$queue->bind('chat', null, ['to' => $user]);
$queue->bind('chat', null, ['from' => $user]);

$exchange->publish(
    json_encode(['action' => sprintf('%s join.', $user)]),
    null,
    AMQP_NOPARAM,
    [
        'headers' => [
            'to' => 'all'
        ]
    ]
);

// send join message
$exchange->publish(json_encode(['action' => sprintf('%s join.', $user)]), 'all');

// Return strictly false if you want to stop consumer
$consumer = function ($envelope) use ($queue) {
    try {
        $body = json_decode($envelope->getBody(), true);

        if (preg_match('(https?:\/\S*)', $body['message'], $matches)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $matches[0]);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode >= 429) {
                throw new \RuntimeException('External server error.');
            }
            $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if ($httpcode == 200 && false !== strstr($mimeType, 'image/')) {
                $body['message'] .= '<br /><img src="' . $matches[0] . '"/>';
            }

            curl_close($ch);
        }

        echo sprintf(
            "id: %d\nevent: %s\ndata: %s\n\n",
            $envelope->getDeliveryTag(),
            'message',
            json_encode($body)
        );

        ob_flush();
        flush();
        $queue->ack($envelope->getDeliveryTag());
    } catch (\Exception $exception) {
        $headers = $envelope->getHeaders();
        if (isset($headers['x-death'][0]['count']) && $headers['x-death'][0]['count'] >= 2) {
            $queue->ack($envelope->getDeliveryTag());
            echo sprintf(
                "id: %d\nevent: %s\ndata: %s\n\n",
                $envelope->getDeliveryTag(),
                'message',
                json_encode($body)
            );
            ob_flush();
            flush();

            return;
        }
        $body['from'] = 'Server';
        $body['message'] = 'Try to show message with image. (retry)';
        echo sprintf(
            "id: %d\nevent: %s\ndata: %s\n\n",
            $envelope->getDeliveryTag(),
            'message',
            json_encode($body)
        );
        ob_flush();
        flush();
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
