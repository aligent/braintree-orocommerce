<?php

namespace Oro\Bundle\UserProBundle;

use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\UserProBundle\Security\TwoFactorFactory;
use Oro\Bundle\UserProBundle\DependencyInjection\Compiler\RolePermissionDatasourcePass;

class OroUserProBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new TwoFactorFactory());
        $container->addCompilerPass(new RolePermissionDatasourcePass());
    }
}
