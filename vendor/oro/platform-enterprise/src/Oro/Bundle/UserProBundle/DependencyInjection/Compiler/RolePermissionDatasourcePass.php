<?php

namespace Oro\Bundle\UserProBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RolePermissionDatasourcePass implements CompilerPassInterface
{
    /** Datagrid service */
    const DATASOURCE_SERVICE = 'oro_user.datagrid.datasource.role_permission_datasource';

    /** Security facade service */
    const SECURITY_FACADE_SERVICE = 'oro_security.security_facade';

    /**
     * Add security facade service to rolePermissionDatasource service
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::DATASOURCE_SERVICE)) {
            return;
        }

        $rolePermissionDefinition = $container->getDefinition(self::DATASOURCE_SERVICE);
        $rolePermissionDefinition->addMethodCall('setSecurityFacade', [new Reference(self::SECURITY_FACADE_SERVICE)]);
    }
}
