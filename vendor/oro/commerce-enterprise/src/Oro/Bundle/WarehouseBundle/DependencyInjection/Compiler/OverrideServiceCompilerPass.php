<?php

namespace Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\WarehouseBundle\ImportExport\Reader\InventoryLevelWithWarehouseReader;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceId = 'oro_inventory.importexport.reader.inventory_level';
        if ($container->hasDefinition($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->setClass(InventoryLevelWithWarehouseReader::class);
            
            $definition->addMethodCall('setSecurityFacade', [new Reference('oro_security.security_facade')]);
        }
    }
}
