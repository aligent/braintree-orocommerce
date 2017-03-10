<?php

namespace Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WarehouseMigrationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition('oro_frontend.class_migration')
            ->addMethodCall(
                'append',
                ['ShippingBundle\\Entity\\ShippingOriginWarehouse', 'WarehouseBundle\\Entity\\WarehouseAddress']
            )
            ->addMethodCall('append', ['oro_shipping_orig_warehouse', 'oro_warehouse_address']);
    }
}
