<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Setting up is the same as the publisher; we open a connection and a channel,
// and declare the queue from which we're going to consume
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// note that we declare the queue here, as well
$channel->queue_declare(
        'task_queue',
        false,
        true, // durable
        false,
        false
    );

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// RabbitMQ doesn't know anything about workers and will still dispatch messages evenly!
// It doesn't look at the number of unacknowledged messages for a consumer.
// It just blindly dispatches every n-th message to the n-th consumer.
// we can use the basic_qos method with the prefetch_count = 1 setting.
// This tells RabbitMQ not to give more than one message to a worker at a time
// Or, in other words, don't dispatch a new message to a worker until it has
// processed and acknowledged the previous one. Instead, it will dispatch it to the next worker that is not still busy.
$channel->basic_qos(null, 1, null);

// tell the server to deliver us the messages from the queue
// Message acknowledgments are turned off by default.
$callback = function($msg) {
    echo ' [x] received ', $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

// we will define a PHP callable that will receive the messages sent by the server.
// https://www.php.net/manual/en/language.types.callable.php
// Keep in mind that messages are sent asynchronously from the server to the clients!

// Using this code we can be sure that even if you kill a worker
// using CTRL+C while it was processing a message, nothing will be lost.
// Soon after the worker dies all unacknowledged messages will be redelivered.
// no_ack -- false
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);





while ($channel->is_consuming()) {
    $channel->wait();
    sleep(2);
}
