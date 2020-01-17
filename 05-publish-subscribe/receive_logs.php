<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Setting up is the same as the publisher; we open a connection and a channel,
// and declare the queue from which we're going to consume
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// note that we declare the exchange here as well in case os worker starts first
$channel->exchange_declare('logs', 'fanout', false, false, false);

// note that we declare the queue here, as well
// create a non-durable queue with a generated name:
list($queue_name, ,) = $channel->queue_declare('', false, false, true, false);

print "Queue name: $queue_name\n";

// We've already created a fanout exchange and a queue
// Now we need to tell the exchange to send messages to our queue!
// That relationship between exchange and a queue is called a binding!
$channel->queue_bind($queue_name, 'logs');

echo " [*] Waiting for messages. To exit press CTRL+C\n";


// tell the server to deliver us the messages from the queue
// Message acknowledgments are turned off by default.
$callback = function($msg) {
    echo ' [x] received ', $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done\n";
};

// we will define a PHP callable that will receive the messages sent by the server.
// https://www.php.net/manual/en/language.types.callable.php
// Keep in mind that messages are sent asynchronously from the server to the clients!

$channel->basic_consume(
    $queue_name,
    '',
    false,
    true,
    false,
    false,
    $callback
);

while ($channel->is_consuming()) {
    $channel->wait();
    sleep(2);
}
