<?php

namespace Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\WarehouseBundle\ImportExport\TemplateFixture\WarehouseInventoryLevelFixture;

class OverrideInventoryTemplateFixtureCompilerPass implements CompilerPassInterface
{
    const INVETORY_TEMPLATE_FIXTURE = 'oro_inventory.importexport.template_fixture.inventory_level';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::INVETORY_TEMPLATE_FIXTURE)) {
            return;
        }

        $inventoryTemplateFixture = $container->getDefinition(self::INVETORY_TEMPLATE_FIXTURE);
        $inventoryTemplateFixture->setClass(WarehouseInventoryLevelFixture::class);
    }
}
