<?php

namespace Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CurrencyCheckerProviderPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition($this->getChainProviderService())) {
            $chainConfigProviderDef = $container->getDefinition($this->getChainProviderService());
            foreach ($container->findTaggedServiceIds($this->getTag()) as $id => $attributes) {
                $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
                $chainConfigProviderDef->addMethodCall('addProvider', [new Reference($id), $priority]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getChainProviderService()
    {
        return 'oro_multi_currency.currency_checker_provider.chain';
    }

    /**
     * @inheritDoc
     */
    protected function getTag()
    {
        return 'oro_multi_currency.currency_checker_provider';
    }
}
