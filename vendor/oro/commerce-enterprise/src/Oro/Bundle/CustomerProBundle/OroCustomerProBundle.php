<?php

namespace Oro\Bundle\CustomerProBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\CustomerProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\CustomerProBundle\DependencyInjection\OroCustomerProExtension;

class OroCustomerProBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new OroCustomerProExtension();
    }
}
