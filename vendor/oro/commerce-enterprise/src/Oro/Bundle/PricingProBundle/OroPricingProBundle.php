<?php

namespace Oro\Bundle\PricingProBundle;

use Oro\Bundle\PricingProBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Oro\Bundle\PricingProBundle\DependencyInjection\OroPricingProExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroPricingProBundle extends Bundle
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
        return new OroPricingProExtension();
    }
}
