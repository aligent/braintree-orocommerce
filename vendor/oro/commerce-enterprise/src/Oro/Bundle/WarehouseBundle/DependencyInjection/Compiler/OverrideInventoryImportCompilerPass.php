<?php

namespace Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideInventoryImportCompilerPass implements CompilerPassInterface
{
    const INVETORY_STATUS_STRATEGY_HELPER = 'oro_inventory.importexport.strategy_helper.inventory_statuses';
    const WAREHOUSE_STRATEGY_HELPER = 'oro_warehouse.importexport.strategy_helper.warehouse';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::INVETORY_STATUS_STRATEGY_HELPER)) {
            return;
        }

        if (!$container->hasDefinition(self::WAREHOUSE_STRATEGY_HELPER)) {
            return;
        }

        $inventoryHelper = $container->getDefinition(self::INVETORY_STATUS_STRATEGY_HELPER);

        $inventoryHelper->addMethodCall('setSuccessor', [new Reference(self::WAREHOUSE_STRATEGY_HELPER)]);
    }
}
