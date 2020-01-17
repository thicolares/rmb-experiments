<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/../vendor/autoload.php';

// The connection abstracts the socket connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// declare a queue for us to send to
// When RabbitMQ quits or crashes it will forget the queues and messages unless you tell it not to.
// Two things are required to make sure that messages aren't lost: we need to mark both the queue
// and messages as durable.

// it won't work in our present setup
// because we've already defined a queue called hello which is not durable
// RabbitMQ doesn't allow you to redefine an existing queue with different parameters
//    $channel->queue_declare(
//        'hello',
//        false,
//        true, // durable!
//        false,
//        false
//    );
// declare a new queue
// This flag set to true needs to be applied to both the producer and consumer code.
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