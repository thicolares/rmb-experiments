<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Setting up is the same as the publisher; we open a connection and a channel,
// and declare the queue from which we're going to consume
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// note that we declare the queue here, as well.
// Because we might start the consumer before the publisher,
// we want to make sure the queue exists before we try to consume messages from it.
//    $channel->queue_declare(
//        'hello',
//        false,
//        false,
//        false,
//        false
//    );
// test rabbitmq by killing its process
$channel->queue_declare(
        'task_queue',
        false,
        true, // durable
        false,
        false
    );

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// tell the server to deliver us the messages from the queue
$callback = function($msg) {
    echo ' [x] received ', $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

// we will define a PHP callable that will receive the messages sent by the server.
// https://www.php.net/manual/en/language.types.callable.php
// Keep in mind that messages are sent asynchronously from the server to the clients!

// Message acknowledgments are turned off by default.

// Using this code we can be sure that even if you kill a worker
// using CTRL+C while it was processing a message, nothing will be lost.
// Soon after the worker dies all unacknowledged messages will be redelivered.
// no_ack -- false
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
    sleep(2);
}
