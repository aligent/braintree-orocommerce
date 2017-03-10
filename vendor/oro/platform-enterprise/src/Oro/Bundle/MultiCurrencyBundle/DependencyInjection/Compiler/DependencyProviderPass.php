<?php

namespace Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @inheritDoc
 */
class DependencyProviderPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition($this->getProviderService())) {
            $providerDefinition = $container->getDefinition($this->getProviderService());
            foreach ($container->findTaggedServiceIds($this->getTag()) as $id => $attributes) {
                $providerDefinition->addMethodCall('addDependency', [new Reference($id)]);
            }
        }
    }

    /**
     * @return string
     */
    protected function getProviderService()
    {
        return 'oro_multi_currency.dependency_config_provider';
    }

    /**
     * @return string
     */
    protected function getTag()
    {
        return 'oro_multicurrency.config.dependency';
    }
}
