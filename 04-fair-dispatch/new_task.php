<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/../vendor/autoload.php';

// The connection abstracts the socket connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// declare a queue for us to send to
$channel->queue_declare(
    'task_queue',
    false,
    true, // durable!
    false,
    false
);

// publish a message to the queue
// declaring a queue is idempotent - it will only be created if it doesn't exist already
$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = 'Hellow World!' . time();
}

echo ' [x] Sent ', $data, "\n";

// we need to mark our messages as persistent
// marking messages as persistent doesn't fully guarantee that a message won't be lost.
// there is still a short time window when RabbitMQ has accepted a message
// and hasn't saved it yet.
// it may be just saved to cache and not really written to the disk.
// If you need a stronger guarantee then you can use https://www.rabbitmq.com/confirms.html
$msg = new AMQPMessage(
    $data,
    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
);
$channel->basic_publish($msg, '', 'task_queue');

$channel->close();
$connection->close();