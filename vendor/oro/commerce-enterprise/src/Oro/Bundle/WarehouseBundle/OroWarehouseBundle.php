<?php

namespace Oro\Bundle\WarehouseBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryImportCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryTemplateFixtureCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\OverrideInventoryDataConverterCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\SetShippingOriginProviderCompilerPass;
use Oro\Bundle\WarehouseBundle\DependencyInjection\Compiler\WarehouseMigrationPass;

class OroWarehouseBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new OverrideInventoryImportCompilerPass());
        $container->addCompilerPass(new OverrideInventoryTemplateFixtureCompilerPass());
        $container->addCompilerPass(new OverrideInventoryDataConverterCompilerPass());
        $container->addCompilerPass(new SetShippingOriginProviderCompilerPass());
        $container->addCompilerPass(new WarehouseMigrationPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'OroInventoryBundle';
    }
}
