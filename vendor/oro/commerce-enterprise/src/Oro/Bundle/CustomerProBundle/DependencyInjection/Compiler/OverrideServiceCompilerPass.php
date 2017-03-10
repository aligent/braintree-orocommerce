<?php

namespace Oro\Bundle\CustomerProBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\CustomerProBundle\Datagrid\RolePermissionDatasource;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = [
            'oro_customer.datagrid.datasource.customer_role_permission_datasource',
            'oro_customer.datagrid.datasource.customer_role_frontend_permission_datasource'
        ];

        foreach ($serviceIds as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);
                $definition->setClass(RolePermissionDatasource::class);
                $definition->addMethodCall('addExcludePermission', ['SHARE']);
            }
        }
    }
}
