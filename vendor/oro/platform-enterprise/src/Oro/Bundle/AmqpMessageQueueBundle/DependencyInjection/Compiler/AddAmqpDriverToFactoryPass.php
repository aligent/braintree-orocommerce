<?php
namespace Oro\Bundle\AmqpMessageQueueBundle\DependencyInjection\Compiler;

use Oro\Component\AmqpMessageQueue\Client\AmqpDriver;
use Oro\Component\AmqpMessageQueue\Transport\Amqp\AmqpConnection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddAmqpDriverToFactoryPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('oro_message_queue.client.driver_factory')) {
            return;
        }

        $driverFactory = $container->getDefinition('oro_message_queue.client.driver_factory');

        $connectionToDriverMap = $driverFactory->getArgument(0);
        $connectionToDriverMap = array_replace($connectionToDriverMap, [
            AmqpConnection::class => AmqpDriver::class,
        ]);

        $driverFactory->replaceArgument(0, $connectionToDriverMap);
    }
}
