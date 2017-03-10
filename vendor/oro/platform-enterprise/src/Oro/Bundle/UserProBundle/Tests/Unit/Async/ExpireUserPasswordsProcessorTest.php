<?php

namespace Oro\Bundle\UserProBundle\Tests\Unit\Async;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;

use Oro\Bundle\UserProBundle\Async\ExpireUserPasswordsProcessor;
use Oro\Bundle\UserProBundle\Async\Topics;

class ExpireUserPasswordsProcessorTest extends \PHPUnit_Framework_TestCase
{
    use MessageQueueExtension;

    public function testShouldPublishMessageToProducer()
    {
        $processor = new ExpireUserPasswordsProcessor(
            self::getMessageProducer(),
            $this->createLoggerMock()
        );


        $userIds = [1, 2];

        $session = new NullSession();
        $message = $session->createMessage(json_encode($userIds));

        $result = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $result);

        self::assertMessageSent(
            Topics::EXPIRE_USER_PASSWORD,
            [
                'userId' => 1,
            ]
        );

        self::assertMessageSent(
            Topics::EXPIRE_USER_PASSWORD,
            [
                'userId' => 2,
            ]
        );
    }

    public function testShouldRejectMessageIfIsNotArray()
    {
        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('[ExpireUserPasswordsProcessor] Got invalid message: ""')
        ;

        $processor = new ExpireUserPasswordsProcessor(
            self::getMessageProducer(),
            $logger
        );

        $session = new NullSession();
        $message = new NullMessage();
        $message->setBody('');

        $result = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $result);
        self::assertMessagesEmpty(Topics::EXPIRE_USER_PASSWORD);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    protected function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
