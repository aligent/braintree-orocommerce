<?php
namespace Oro\Component\AmqpMessageQueue\Client;

use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpMessage;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpQueue;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpSession;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpTopic;
use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Client\DriverInterface;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Transport\Exception\InvalidDestinationException;
use Oro\Component\MessageQueue\Transport\QueueInterface;

class AmqpDriver implements DriverInterface
{
    /**
     * @var AmqpSession
     */
    protected $session;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $priorityMap;

    /**
     * @param AmqpSession $session
     * @param Config               $config
     */
    public function __construct(AmqpSession $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;

        $this->priorityMap = [
            MessagePriority::VERY_LOW => 0,
            MessagePriority::LOW => 1,
            MessagePriority::NORMAL => 2,
            MessagePriority::HIGH => 3,
            MessagePriority::VERY_HIGH => 4,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param AmqpQueue $queue
     */
    public function send(QueueInterface $queue, Message $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($queue, AmqpQueue::class);

        $destination = $queue;

        $headers = $message->getHeaders();
        $properties = $message->getProperties();

        $headers['content_type'] = $message->getContentType();

        if ($message->getExpire()) {
            $headers['expiration'] = (string) ($message->getExpire() * 1000);
        }

        if ($message->getDelay()) {
            $properties['x-delay'] = (string) ($message->getDelay() * 1000);

            $destination = $this->createDelayedTopic($queue);
        }

        $headers['delivery_mode'] = AmqpMessage::DELIVERY_MODE_PERSISTENT;

        $transportMessage = $this->createTransportMessage();
        $transportMessage->setBody($message->getBody());
        $transportMessage->setHeaders($headers);
        $transportMessage->setProperties($properties);
        $transportMessage->setMessageId($message->getMessageId());
        $transportMessage->setTimestamp($message->getTimestamp());

        if ($message->getPriority()) {
            $this->setMessagePriority($transportMessage, $message->getPriority());
        }

        $this->session->createProducer()->send($destination, $transportMessage);
    }

    /**
     * @param string $queueName
     *
     * @return QueueInterface
     */
    public function createQueue($queueName)
    {
        $queue = $this->session->createQueue($queueName);
        $queue->setDurable(true);
        $queue->setAutoDelete(false);
        $queue->setTable(['x-max-priority' => 4]);
        $this->session->declareQueue($queue);

        return $queue;
    }

    /**
     * {@inheritdoc}
     *
     * @return AmqpMessage
     */
    public function createTransportMessage()
    {
        return $this->session->createMessage();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param AmqpMessage $message
     * @param string $priority
     */
    private function setMessagePriority(AmqpMessage $message, $priority)
    {
        if (false == array_key_exists($priority, $this->priorityMap)) {
            throw new \InvalidArgumentException(sprintf(
                'Given priority could not be converted to transport\'s one. Got: %s',
                $priority
            ));
        }

        $headers = $message->getHeaders();
        $headers['priority'] = $this->priorityMap[$priority];
        $message->setHeaders($headers);
    }

    /**
     * @param AmqpQueue $queue
     *
     * @return AmqpTopic
     */
    private function createDelayedTopic(AmqpQueue $queue)
    {
        $queueName = $queue->getQueueName();

        // in order to use delay feature make sure the rabbitmq_delayed_message_exchange plugin is installed.
        $delayTopic = $this->session->createTopic($queueName.'.delayed');
        $delayTopic->setType('x-delayed-message');
        $delayTopic->setDurable(true);
        $delayTopic->setTable([
            'x-delayed-type' => 'direct',
        ]);
        $this->session->declareTopic($delayTopic);

        $this->session->declareBind($delayTopic, $queue);

        return $delayTopic;
    }
}
