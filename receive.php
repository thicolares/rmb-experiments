<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Setting up is the same as the publisher; we open a connection and a channel,
// and declare the queue from which we're going to consume
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// note that we declare the queue here, as well.
// Because we might start the consumer before the publisher,
// we want to make sure the queue exists before we try to consume messages from it.
$channel->queue_declare('hello', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// tell the server to deliver us the messages from the queue
$callback = function($msg) {
    echo ' [x] received ', $msg->body, "\n";
};

// we will define a PHP callable that will receive the messages sent by the server.
// https://www.php.net/manual/en/language.types.callable.php
// Keep in mind that messages are sent asynchronously from the server to the clients!
$channel->basic_consume('hello', '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
    sleep(2);
}
