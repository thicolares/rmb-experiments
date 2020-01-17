<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/vendor/autoload.php';

// The connection abstracts the socket connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// declare a queue for us to send to
$channel->queue_declare('hello', false, false, false, false);

// publish a message to the queue
// declaring a queue is idempotent - it will only be created if it doesn't exist already
$msg = new AMQPMessage('Hellow World!' . time());
$channel->basic_publish($msg, '', 'hello');

$channel->close();
$connection->close();