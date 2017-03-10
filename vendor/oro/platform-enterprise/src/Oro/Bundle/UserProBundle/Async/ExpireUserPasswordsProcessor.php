<?php

namespace Oro\Bundle\UserProBundle\Async;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class ExpireUserPasswordsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var MessageProducerInterface */
    protected $producer;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param MessageProducerInterface $producer
     * @param LoggerInterface          $logger
     */
    public function __construct(MessageProducerInterface $producer, LoggerInterface $logger)
    {
        $this->producer = $producer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $userIds = JSON::decode($message->getBody());

        if (false == is_array($userIds)) {
            $this->logger->critical(
                sprintf(
                    '[ExpireUserPasswordsProcessor] Got invalid message: "%s"',
                    $message->getBody()
                ),
                ['message' => $message]
            );

            return self::REJECT;
        }

        foreach ($userIds as $userId) {
            $this->producer->send(
                Topics::EXPIRE_USER_PASSWORD,
                [
                    'userId' => $userId,
                ]
            );
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::EXPIRE_USER_PASSWORDS];
    }
}
