<?php
namespace Oro\Component\AmqpMessageQueue\Tests\Unit\Transport\Amqp;

use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpMessage;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\Testing\ClassExtensionTrait;

class AmqpMessageTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageInterface()
    {
        $this->assertClassImplements(MessageInterface::class, AmqpMessage::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new AmqpMessage();
    }

    public function testShouldNewMessageReturnEmptyBody()
    {
        $message = new AmqpMessage();

        $this->assertSame(null, $message->getBody());
    }

    public function testShouldNewMessageReturnEmptyProperties()
    {
        $message = new AmqpMessage();

        $this->assertSame([], $message->getProperties());
    }

    public function testShouldNewMessageReturnEmptyHeaders()
    {
        $message = new AmqpMessage();

        $this->assertSame([], $message->getHeaders());
    }

    public function testShouldAllowGetPreviouslySetBody()
    {
        $message = new AmqpMessage();

        $message->setBody('theBody');

        $this->assertSame('theBody', $message->getBody());
    }

    public function testShouldAllowGetPreviouslySetHeaders()
    {
        $message = new AmqpMessage();

        $message->setHeaders(['foo' => 'fooVal']);

        $this->assertSame(['foo' => 'fooVal'], $message->getHeaders());
    }

    public function testShouldAllowGetByNamePreviouslySetHeader()
    {
        $message = new AmqpMessage();

        $message->setHeaders(['foo' => 'fooVal']);

        $this->assertSame('fooVal', $message->getHeader('foo'));
    }

    public function testShouldAllowGetPreviouslySetProperties()
    {
        $message = new AmqpMessage();

        $message->setProperties(['foo' => 'fooVal']);

        $this->assertSame(['foo' => 'fooVal'], $message->getProperties());
    }

    public function testShouldAllowGetByNamePreviouslySetProperty()
    {
        $message = new AmqpMessage();

        $message->setProperties(['foo' => 'fooVal']);

        $this->assertSame('fooVal', $message->getProperty('foo'));
    }

    public function testShouldReturnDefaultIfPropertyNotSet()
    {
        $message = new AmqpMessage();

        $message->setProperties(['foo' => 'fooVal']);

        $this->assertSame('barDefault', $message->getProperty('bar', 'barDefault'));
    }

    public function testShouldReturnDefaultIfHeaderNotSet()
    {
        $message = new AmqpMessage();

        $message->setHeaders(['foo' => 'fooVal']);

        $this->assertSame('barDefault', $message->getHeader('bar', 'barDefault'));
    }

    public function testShouldSetNullDeliveryKeyInConstructor()
    {
        $message = new AmqpMessage();

        $this->assertSame(null, $message->getDeliveryTag());
    }

    public function testShouldAllowGetPreviouslySetRoutingKey()
    {
        $message = new AmqpMessage();
        $message->setDeliveryTag('theDeliveryKey');

        $this->assertEquals('theDeliveryKey', $message->getDeliveryTag());
    }

    public function testShouldSetRedeliveredFalseInConstructor()
    {
        $message = new AmqpMessage();

        $this->assertFalse($message->isRedelivered());
    }

    public function testShouldAllowGetPreviouslySetRedelivered()
    {
        $message = new AmqpMessage();
        $message->setRedelivered(true);

        $this->assertTrue($message->isRedelivered());
    }

    public function testShouldReturnEmptyStringAsDefaultCorrelationId()
    {
        $message = new AmqpMessage();

        self::assertSame('', $message->getCorrelationId());
    }

    public function testShouldAllowGetPreviouslySetCorrelationId()
    {
        $message = new AmqpMessage();
        $message->setCorrelationId('theId');

        self::assertSame('theId', $message->getCorrelationId());
    }

    public function testShouldCastCorrelationIdToStringOnSet()
    {
        $message = new AmqpMessage();
        $message->setCorrelationId(123);

        self::assertSame('123', $message->getCorrelationId());
    }

    public function testShouldReturnEmptyStringAsDefaultMessageId()
    {
        $message = new AmqpMessage();

        self::assertSame('', $message->getMessageId());
    }

    public function testShouldAllowGetPreviouslySetMessageId()
    {
        $message = new AmqpMessage();
        $message->setMessageId('theId');

        self::assertSame('theId', $message->getMessageId());
    }

    public function testShouldCastMessageIdToStringOnSet()
    {
        $message = new AmqpMessage();
        $message->setMessageId(123);

        self::assertSame('123', $message->getMessageId());
    }

    public function testShouldReturnNullAsDefaultTimestamp()
    {
        $message = new AmqpMessage();

        self::assertSame(null, $message->getTimestamp());
    }

    public function testShouldAllowGetPreviouslySetTimestamp()
    {
        $message = new AmqpMessage();
        $message->setTimestamp(123);

        self::assertSame(123, $message->getTimestamp());
    }

    public function testShouldCastTimestampToIntOnSet()
    {
        $message = new AmqpMessage();
        $message->setTimestamp('123');

        self::assertSame(123, $message->getTimestamp());
    }
}
