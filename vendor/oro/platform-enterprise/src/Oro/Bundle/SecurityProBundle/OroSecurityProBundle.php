<?php

namespace Oro\Bundle\SecurityProBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\SecurityProBundle\DependencyInjection\Compiler\OverrideEntityAclExtensionPass;
use Oro\Bundle\SecurityProBundle\DependencyInjection\Compiler\OverrideSecurityTokenFactories;
use Oro\Bundle\SecurityProBundle\DependencyInjection\Compiler\OroProAclConfigurationPass;

class OroSecurityProBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideEntityAclExtensionPass());
        $container->addCompilerPass(new OverrideSecurityTokenFactories());
        $container->addCompilerPass(new OroProAclConfigurationPass());
    }
}
