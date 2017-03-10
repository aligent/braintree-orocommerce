<?php
namespace Oro\Bundle\AmqpMessageQueueBundle;

use Oro\Bundle\AmqpMessageQueueBundle\DependencyInjection\Compiler\AddAmqpDriverToFactoryPass;
use Oro\Bundle\MessageQueueBundle\DependencyInjection\OroMessageQueueExtension;
use Oro\Component\AmqpMessageQueue\DependencyInjection\AmqpTransportFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroAmqpMessageQueueBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddAmqpDriverToFactoryPass());

        /** @var OroMessageQueueExtension $extension */
        $extension = $container->getExtension('oro_message_queue');
        $extension->addTransportFactory(new AmqpTransportFactory());
    }
}
