<?php
/**
 * Compiler pass to add config builders to the main chain config builder
 *
 * @category  Braintree
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting
 * @license   MIT
 * @link      http://www.aligent.com.au/
 **/

namespace Aligent\BraintreeBundle\DependencyInjection\Compiler;

use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\ChainConfigurationBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PaymentMethodConfigurationPass implements CompilerPassInterface
{
    const ALIGENT_BRAINTREE_PAYMENT_METHOD_SETTINGS_TAG = 'braintree.payment_method_settings';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has(ChainConfigurationBuilder::class)) {
            return;
        }

        $definition = $container->findDefinition(ChainConfigurationBuilder::class);

        // find all service IDs with the braintree.payment_method_settings tag
        $taggedServices = $container->findTaggedServiceIds(self::ALIGENT_BRAINTREE_PAYMENT_METHOD_SETTINGS_TAG);

        foreach ($taggedServices as $id => $tags) {
            // a service could have the same tag twice
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addBuilder',
                    [
                        $attributes["payment_method"],
                        new Reference($id)
                    ]
                );
            }
        }
    }
}
