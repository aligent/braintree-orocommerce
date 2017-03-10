<?php

namespace Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SetShippingOriginProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $argumentId = 'oro_shipping.shipping_origin.provider';
        $serviceId = 'oro_warehouse.warehouse_address.provider';
        if (!$container->hasDefinition($serviceId) || !$container->hasDefinition($argumentId)) {
            return;
        }

        $definition = $container->getDefinition($serviceId);

        $definition->addMethodCall('setShippingOriginProvider', [new Reference($argumentId)]);
    }
}
