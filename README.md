# Overview

* A **producer** is a user application that sends messages.
* A **queue** is a buffer that stores messages.
* A **consumer** is a user application that receives messages.
* A producer never sends any messages directly to a queue!
* Instead, the producer can only send messages to an **exchange**.
* **Exchange**:
    * on one side it receives messages from producers and the other side it pushes them to queues.
    * the rules for that are defined by the **exchange type** (AKA topology).
    * there are a few exchange types available: direct, topic, headers and fanout.


# 04 Fair dispatch

* each task is delivered to exactly one worker
* We define that on each worker by setting
* we sent and received messages to and from a queue

```
$channel->basic_qos(null, 1, null);
```

# 05 Publish/Subscribe

* deliver a message to multiple consumers
* this pattern is known as "publish/subscribe".
* this example: a log system
* the first will emit log messages and the second will receive and print them
* published log messages are going to be broadcast to all the receivers
* let's create an exchange of the type `fanout`, and call it **logs**
* previously we were using a default exchange, which we identify by the empty string (""): `$channel->basic_publish($msg, '', 'hello');`
    * the default or nameless exchange: messages are routed to the queue with the name specified by `routing_key`, if it exists
    * the routing key is the third argument to `basic_publish`