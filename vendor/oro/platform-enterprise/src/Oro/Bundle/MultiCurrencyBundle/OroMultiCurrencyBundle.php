<?php

namespace Oro\Bundle\MultiCurrencyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Compiler\CurrencyCheckerProviderPass;
use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Compiler\DependencyProviderPass;

class OroMultiCurrencyBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CurrencyCheckerProviderPass());
        $container->addCompilerPass(new DependencyProviderPass());
    }
}
