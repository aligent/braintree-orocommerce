<?php
namespace Oro\Component\AmqpMessageQueue\Tests\Unit\Client;

use Oro\Component\AmqpMessageQueue\Client\AmqpDriver;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpMessage;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpMessageProducer;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpQueue;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpSession;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpTopic;
use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class AmqpDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithRequiredArguments()
    {
        new AmqpDriver($this->createSessionMock(), new Config('', '', '', '', ''));
    }

    public function testShouldSendJustCreatedMessageToQueue()
    {
        $config = new Config('', '', '', '', '');
        $queue = new AmqpQueue('aQueue');

        $transportMessage = new AmqpMessage();

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
            ->with(self::identicalTo($queue), self::identicalTo($transportMessage))
        ;

        $session = $this->createSessionStub($transportMessage, $producer);

        $driver = new AmqpDriver($session, $config);

        $driver->send($queue, new Message());
    }

    public function testShouldConvertClientMessageToTransportMessage()
    {
        $config = new Config('', '', '', '', '');
        $queue = new AmqpQueue('aQueue');

        $message = new Message();
        $message->setBody('theBody');
        $message->setContentType('theContentType');
        $message->setMessageId('theMessageId');
        $message->setTimestamp(12345);
        $message->setHeaders(['theHeaderFoo' => 'theFoo']);
        $message->setProperties(['thePropertyBar' => 'theBar']);

        $transportMessage = new AmqpMessage();

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
        ;

        $session = $this->createSessionStub($transportMessage, $producer);

        $driver = new AmqpDriver($session, $config);

        $driver->send($queue, $message);

        self::assertSame('theBody', $transportMessage->getBody());
        self::assertSame([
            'theHeaderFoo' => 'theFoo',
            'content_type' => 'theContentType',
            'delivery_mode' => AmqpMessage::DELIVERY_MODE_PERSISTENT,
            'message_id' => 'theMessageId',
            'timestamp' => 12345
        ], $transportMessage->getHeaders());
        self::assertSame([
            'thePropertyBar' => 'theBar',
        ], $transportMessage->getProperties());
    }

    public function testShouldSetExpirationHeaderInMillisecondsIfSetInClientMessage()
    {
        $config = new Config('', '', '', '', '');
        $queue = new AmqpQueue('aQueue');

        $message = new Message();
        $message->setExpire(123);

        $transportMessage = new AmqpMessage();

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
        ;

        $session = $this->createSessionStub($transportMessage, $producer);

        $driver = new AmqpDriver($session, $config);
        $driver->send($queue, $message);

        self::assertSame('123000', $transportMessage->getHeader('expiration'));
    }

    /**
     * @dataProvider providePriorities
     */
    public function testCorrectlyConvertClientsPriorityToTransportsPriority($clientPriority, $transportPriority)
    {
        $config = new Config('', '', '', '', '');
        $queue = new AmqpQueue('aQueue');

        $message = new Message();
        $message->setPriority($clientPriority);

        $transportMessage = new AmqpMessage();

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
        ;

        $session = $this->createSessionStub($transportMessage, $producer);

        $driver = new AmqpDriver($session, $config);

        $driver->send($queue, $message);

        self::assertSame($transportPriority, $transportMessage->getHeader('priority'));
    }

    public function testShouldReturnConfigInstance()
    {
        $config = new Config('', '', '', '', '');

        $driver = new AmqpDriver($this->createSessionMock(), $config);
        $result = $driver->getConfig();

        self::assertSame($config, $result);
    }

    public function testAllowCreateTransportMessage()
    {
        $config = new Config('', '', '', '', '');

        $message = new AmqpMessage();

        $session = $this->createSessionMock();
        $session
            ->expects(self::once())
            ->method('createMessage')
            ->willReturn($message)
        ;

        $driver = new AmqpDriver($session, $config);

        self::assertSame($message, $driver->createTransportMessage());
    }

    public function testShouldCreateQueueWithExpectedParameters()
    {
        $queue = new AmqpQueue('');

        $config = new Config('', '', '', '', '');

        $session = $this->createSessionMock();
        $session
            ->expects($this->once())
            ->method('createQueue')
            ->with('queue-name')
            ->will($this->returnValue($queue))
        ;
        $session
            ->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
        ;

        $driver = new AmqpDriver($session, $config);
        $result = $driver->createQueue('queue-name');

        self::assertSame($queue, $result);

        self::assertEmpty($queue->getConsumerTag());
        self::assertFalse($queue->isExclusive());
        self::assertFalse($queue->isAutoDelete());
        self::assertFalse($queue->isPassive());
        self::assertFalse($queue->isNoWait());
        self::assertTrue($queue->isDurable());
        self::assertFalse($queue->isNoAck());
        self::assertFalse($queue->isNoLocal());
        self::assertEquals(['x-max-priority' => 4], $queue->getTable());
    }

    public function testShouldSendMessageToDelayedTopicIfDelayIsSet()
    {
        $config = new Config('', '', '', '', '');
        $queue = new AmqpQueue('theQueueName');
        $delayTopic = new AmqpTopic('theQueueName.delayed');

        $message = new Message();
        $message->setDelay(123);

        $transportMessage = new AmqpMessage();

        $producer = $this->createMessageProducer();
        $producer
            ->expects(self::once())
            ->method('send')
            ->with(self::identicalTo($delayTopic), self::identicalTo($transportMessage))
        ;

        $sessionMock = $this->createSessionStub($transportMessage, $producer);
        $sessionMock
            ->expects($this->once())
            ->method('createTopic')
            ->with('theQueueName.delayed')
            ->willReturn($delayTopic)
        ;
        $sessionMock
            ->expects($this->once())
            ->method('declareTopic')
            ->with(self::identicalTo($delayTopic))
        ;
        $sessionMock
            ->expects($this->once())
            ->method('declareBind')
            ->with(self::identicalTo($delayTopic), self::identicalTo($queue))
        ;

        $driver = new AmqpDriver($sessionMock, $config);
        $driver->send($queue, $message);

        self::assertSame('123000', $transportMessage->getProperty('x-delay'));
        self::assertEquals('x-delayed-message', $delayTopic->getType());
        self::assertTrue($delayTopic->isDurable());
        self::assertFalse($delayTopic->isImmediate());
        self::assertFalse($delayTopic->isPassive());
        self::assertFalse($delayTopic->isMandatory());
        self::assertFalse($delayTopic->isNoWait());
        self::assertEquals(['x-delayed-type' => 'direct'], $delayTopic->getTable());
    }

    public function providePriorities()
    {
        return [
            [MessagePriority::VERY_LOW, 0],
            [MessagePriority::LOW, 1],
            [MessagePriority::NORMAL, 2],
            [MessagePriority::HIGH, 3],
            [MessagePriority::VERY_HIGH, 4],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmqpSession
     */
    private function createSessionStub($message = null, $messageProducer = null)
    {
        $sessionMock = $this->createMock(AmqpSession::class);
        $sessionMock
            ->expects($this->any())
            ->method('createMessage')
            ->willReturn($message)
        ;
        $sessionMock
            ->expects($this->any())
            ->method('createQueue')
            ->willReturnCallback(function ($name) {
                return new AmqpQueue($name);
            })
        ;
        $sessionMock
            ->expects($this->any())
            ->method('createProducer')
            ->willReturn($messageProducer)
        ;

        return $sessionMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmqpMessageProducer
     */
    private function createMessageProducer()
    {
        return $this->createMock(AmqpMessageProducer::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmqpSession
     */
    private function createSessionMock()
    {
        return $this->createMock(AmqpSession::class);
    }
}
