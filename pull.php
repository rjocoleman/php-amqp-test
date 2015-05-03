<?php

require_once __DIR__.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;

function queuePull($routing_key='sample', $queue='example')
{
    echo 'Pulling from Routing Key: ' . $routing_key . "\n";
    echo 'Using queue: ' . $queue . "\n";

    $_exchangeName = 'eventful';

    $connection = _getRabbitMq();
    $channel = $connection->channel();
    /*
        name: $queue
        passive: false
        durable: true // the queue will survive server restarts.
        exclusive: false // the queue can be accessed in other channels.
        auto_delete: false // the queue won't be deleted once the channel is closed.
        nowait: false // doesn't wait on replies for certain things.
        parameters: array // how you send certain extra data to the queue declare.
    */
    $channel->queue_declare($queue, false, true, false, false, false);
    /*
        name: $exchange
        type: topic
        passive: false
        durable: true // the exchange will survive server restarts.
        auto_delete: false // the exchange won't be deleted once the channel is closed.
    */
    $channel->exchange_declare($_exchangeName, 'topic', false, true, false);
    $channel->queue_bind($queue, $_exchangeName, $routing_key);

    $msg = $channel->basic_get($queue);

    if ($msg) {
        $channel->basic_ack($msg->delivery_info['delivery_tag']);
        echo $msg->body;
    }

    $channel->close();
    _getRabbitMq()->close();;
}

function _getRabbitMq()
{
    $parsed_url = parse_url(getenv('RABBITMQ_URL'));
    $_rabbitMq = new AMQPConnection(
      $parsed_url['host'],
      isset($parsed_url['port']) ? $parsed_url['port'] : '5672',
      $parsed_url['user'],
      $parsed_url['pass'],
      ltrim($parsed_url['path'], '/')
    );
    return $_rabbitMq;
}

queuePull($argv[2], $argv[1]);

?>
