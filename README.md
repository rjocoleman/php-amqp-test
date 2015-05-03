PoC RabbitMQ Pub/Sub + Pull
===========================

A proof of concept for Topic Based Pub/Sub using persistant messages, durable queues and publisher confirms.

In this architecture each event has a `type` (external to RabbitMQ), events are published to RabbitMQ with their `type` as the RabbitMQ `routing_key`.

Subscriber's that are interested in this `type` of event bind to that routing key with their own queues. Each time a message is published it's sent to the queue for each subscriber. It's then up to the subscriber to retrieve and acknowlege the message.

* `publish.php` - sends an arbitrary message.
* `subscribe.php` - subscribes to a queue and wait's for messages.
* `pull.php` - grabs a single message off the queue and acknowleges it.

Install
=======

* Get dependancies: `$ composer.phar install`
* Set up RabbitMQ and formulate a connection string.

Usage
=====

Each subscriber that you want to recieve the message needs to have it's own `queue` declared.
The subscriber's queues will be bound to a `routing_key` and will receive any messages for that key.

In short, for each discrete consumer use a discrete queue.

### Subscribing

Subscribe and wait for messages.

Terminal 1: `RABBITMQ_URL=amqp://bus.megacorp.internal:5766/party php -f subscribe.php queue1 type`

Terminal 2: `RABBITMQ_URL=amqp://bus.megacorp.internal:5766/party php -f subscribe.php queue2 type`

Terminal 3: `RABBITMQ_URL=amqp://bus.megacorp.internal:5766/party php -f publish.php type`

A message should appear in Terminal 1 and Terminal 2 for each time you run the command on Terminal 3.

### Pulling

Pull needs to have been bound to a queue before it'll have messages routed to it (i.e. run it, first time nothing will be recieved, then all published messages from then for that `routing_key` will be stored for retrieval).

Terminal 4: `RABBITMQ_URL=amqp://bus.megacorp.internal:5766/party php -f pull.php queue3 type`

A message should appear in Terminal 4 each time it's run (after a message has been published of course).

#### Note

If you set two subscribers or pull with the same `queue` name only one will get it i.e. each app that wants to know about a message should use it's own queue. In the above examples each terminal is it's own app.