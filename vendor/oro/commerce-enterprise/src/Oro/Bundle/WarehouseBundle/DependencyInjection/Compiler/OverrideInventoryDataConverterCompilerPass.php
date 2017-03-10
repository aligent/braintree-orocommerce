<?php

namespace Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\WarehouseBundle\ImportExport\DataConverter\WarehouseInventoryLevelDataConverter;

class OverrideInventoryDataConverterCompilerPass implements CompilerPassInterface
{
    const INVETORY_DATA_CONVERTER = 'oro_inventory.importexport.inventory_level_converter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::INVETORY_DATA_CONVERTER)) {
            return;
        }

        $inventoryTemplateFixture = $container->getDefinition(self::INVETORY_DATA_CONVERTER);
        $inventoryTemplateFixture->setClass(WarehouseInventoryLevelDataConverter::class);
    }
}
