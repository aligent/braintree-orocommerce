<?php

namespace Oro\Bundle\MultiWebsiteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\MultiWebsiteBundle\EventListener\CustomerFormViewListener;
use Oro\Bundle\MultiWebsiteBundle\EventListener\CustomerGroupFormViewListener;
use Oro\Bundle\MultiWebsiteBundle\EventListener\PriceListFormViewListener;
use Oro\Bundle\MultiWebsiteBundle\Provider\WebsiteProvider;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceId = 'oro_pricing.event_listener.price_list_form_view';
        if ($container->hasDefinition($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->setClass(PriceListFormViewListener::class);
        }

        $serviceId = 'oro_website.website.provider';
        if ($container->hasDefinition($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->setClass(WebsiteProvider::class);
        }

        $serviceId = 'oro_pricing.event_listener.customer_form_view';
        if ($container->hasDefinition($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->setClass(CustomerFormViewListener::class);
            $definition->addArgument(new Reference('oro_website.website.provider'));
        }

        $serviceId = 'oro_pricing.event_listener.customer_group_form_view';
        if ($container->hasDefinition($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->setClass(CustomerGroupFormViewListener::class);
            $definition->addArgument(new Reference('oro_website.website.provider'));
        }
    }
}
