<?php
namespace Oro\Component\AmqpMessageQueue\Tests\Unit\Client;

use Oro\Component\AmqpMessageQueue\Client\AmqpDriver;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpConnection;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpSession;
use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Client\DriverFactory;
use PhpAmqpLib\Connection\AbstractConnection;

class AmqpDriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldCreateAmpqDriverInstance()
    {
        $config = new Config('', '', '', '');

        $amqpConnection = $this->createMock(AbstractConnection::class);
        $connection = new AmqpConnection($amqpConnection);

        $factory = new DriverFactory([AmqpConnection::class => AmqpDriver::class]);
        $driver = $factory->create($connection, $config);

        self::assertInstanceOf(AmqpDriver::class, $driver);
        self::assertAttributeInstanceOf(AmqpSession::class, 'session', $driver);
        self::assertAttributeSame($config, 'config', $driver);
    }
}
