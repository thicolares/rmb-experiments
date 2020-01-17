<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/../vendor/autoload.php';

// The connection abstracts the socket connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// we can publish to our named exchange instead now
// fanout: it just broadcasts all the messages it receives to all the queues it knows
$channel->exchange_declare(
    'logs',
    'fanout',
    false,
    false,
    false
);

// declare a queue for us to send to
//$channel->queue_declare(
//    'task_queue',
//    false,
//    false,
//    false,
//    false
//);

// publish a message to the queue
// declaring a queue is idempotent - it will only be created if it doesn't exist already
$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = time() . ' INFO Hellow World!';
}

$msg = new AMQPMessage($data);

// previously we were using a default exchange, which we identify by the empty string ("")
// the default or nameless exchange: messages are routed to the queue with the name specified by `routing_key`, if it exists
// the routing key is the third argument to basic_publish
// $channel->basic_publish($msg, '', 'task_queue');
// we can publish to our named exchange instead:
$channel->basic_publish($msg, 'logs');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();