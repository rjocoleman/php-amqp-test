<?php

require_once __DIR__.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

function _getMessageOptions($routing_key)
{
    array(
        'content_type' => 'application/json',
        'routing_key'  => $routing_key,
        'timestamp'    => 123,
        'persistent'   => true,
    );
}

function postMessage($routing_key, $message)
{
    echo 'Publishing to Routing Key: ' . $routing_key . "\n";

    $_exchangeName = 'eventful';

    $payload = array(
        "type"        => $routing_key,
        "occurred_at" => '',
        "source"      => "magento",
        "uuid"        => 'bar',
        "data"        => json_decode($message, true),
    );

    $connection = _getRabbitMq();
    $channel = $connection->channel();
    $channel->confirm_select();
    /*
        name: $exchange
        type: topic
        passive: false
        durable: true // the exchange will survive server restarts.
        auto_delete: false // the exchange won't be deleted once the channel is closed.
    */
    $channel->exchange_declare($_exchangeName, 'topic', false, true, false);

    $AmqpMessage = new AMQPMessage(json_encode($payload), _getMessageOptions($routing_key));
    $channel->basic_publish($AmqpMessage, $_exchangeName, $routing_key);

    $channel->wait_for_pending_acks();
    $channel->close();
    _getRabbitMq()->close();
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

postMessage($argv[1], '{"foo": true}');

?>
